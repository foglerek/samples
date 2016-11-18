<?php namespace Anon\Filters;

use Exception;

/**
 * Route parameters suffixed by Idf or Identifier get
 * automatically translated to Ids before being passed to controller.
 *
 * The parameter name must match an existing repository implementing the
 * CachedIdentifierInterface.
 *
 * The filter can be skipped by prepending _ to any parameter.
 * The _ will be stripped out of the actual parameter name after passing
 * through the filter.
 *
 * Any other route parameters are left untouched.
 *
 *
 * @example
 *
 * Route Definition:
 *
 *  /api/parent/{parentIdentifier}/child/{_childIdf}/grandchild/{grandchildId}
 *
 * Request:
 *
 *  /api/parent/abc123/child/def456/grandchild/789
 *
 * Route Parameters get translated to:
 *
 *  [
 *      'parentIdentifier': ParentRepository::getIdFromIdentifier('abc123'),
 *      'childIdf': def456, // Ignored due to the prepended underscore. Underscore is stripped out.
 *      'grandchildId': 789 // Ignored due to not being suffixed by Identifier or Idf.
 *  ]
 */
class IdentifierTranslator
{
    const IDF = 'Idf';
    const IDENTIFIER = 'Identifier';

    /**
     * This dependency injection was added to the code snippet
     * to make testing work without needing to install the whole
     * Laravel framework.
     *
     * Mocking Laravel Facades outside of Laravel is
     * pretty gnarly.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param Illuminate\Routing\Route $route Current Route Object
     * @param Illuminate\Http\Request $request Current Request Object
     *
     * @return void
     */
    public function filter($route, $request)
    {
        foreach($route->parameters() as $param => $identifier) {
            // Match route parameters ending with Idf or Identifier and snip their suffix.
            $type = substr($param, - strlen(self::IDF)) === self::IDF ?
                substr($param, 0, strlen($param) - strlen(self::IDF)) : (
                    substr($param, - strlen(self::IDENTIFIER)) === self::IDENTIFIER ?
                        substr($param, 0, strlen($param) - strlen(self::IDENTIFIER)) : null
                );

            if (!is_null($type)) {
                // If the parameter begins with an underscore,
                // we strip the underscore out and don't translate the identifier.
                if ($param[0] === '_') {
                    $route->forgetParameter($param);
                    $route->setParameter(substr($param, 1), $identifier);
                    // Move on to next parameter.
                    continue;
                }

                // CamelCase the name.
                $repoName = str_replace(' ', '', ucwords(str_replace('-', ' ', $type))) . 'Repository';
                $repoIOCName = $repoName . 'Interface';
                $repo = null;

                // Try to resolve repo from IOC container.
                try {
                    $repo = $this->app->make($repoIOCName);
                } catch (Exception $e) {
                    throw new IdentifierTranslatorException(sprintf('Repository \'%s\' not found in the IOC Container. Could not map route parameter \'%s\' to an existing repository. If you would like the parameter to be ignored by this filter, please prefix it with \'_\'.', $repoIOCName, $param));
                }

                // Perform Identifier->Id translation of route parameter
                // if its corresponding repository implements the CachedIdentifierInterface.
                if (in_array('Anon\Support\Contracts\CachedIdentifierInterface', class_implements($repo))) {
                    $id = null;
                    try {
                        $id = $repo->getIdFromIdentifier($identifier);
                    } catch (Exception $e) {
                        throw new IdentifierTranslatorException(sprintf('Could not translate identifier \'%s\' using %s->getIdFromIdentifier().', $identifier, $repoName), 0, $e);
                    }
                    $route->setParameter($param, $id);
                } else {
                    throw new IdentifierTranslatorException(sprintf('Repository %s does not implement the CachedIdentifierInterface and cannot be automatically translated. Please make sure that route parameter \'%s\' is correctly named. If you would like the parameter to be ignored by this filter, please prefix it with \'_\'.', $repoName, $param));
                }
            }
        }
    }
}

class IdentifierTranslatorException extends Exception {};
