### About
The main piece of code resides in `IdentifierTranslator.php`.

It is a Route filter for the Laravel Framework. It intercepts a request to a particular route, and automatically translates any parameter containing an alphanumeric resource identifier to its numeric counterpart, before the parameter gets passed on to the Controller level.

This allows us to use alpha-numeric/hashed identifiers in public-facing applications (not leaking information about
number of resources), while still strictly using numeric identifiers on the backend, without needing to perform
translation in every Controller method.

The setup of the codebase is:
```
Routes -> Filters -> Controllers -> Services -> Repositories -> Models -> Database
```

The Repository corresponding to the resource performs the actual translation, provided that it implements the CachedIdentfierInterface.

### Example

Given the following route definition:

`/api/parent/{parentIdentifier}/child/{childIdf}/grandchild/{grandchildId}`

and the following request:

`/api/parent/abc123/child/def456/grandchild/789`

the filter will attempt to translate any route parameters with `Identifier` or `Idf` as their suffix, in this case `parentIdentifier` and `childIdf`. It will query ParentRepository and ChildRepository, and translate abc123 and def456 to their numeric counterparts used on the backend, before passing them on to the Controller layer. The last parameter will be left untouched.

The filter handles various cases, such as a missing Repository or cases where the Repository does not implement the correct interface. The filter also supports ignoring route parameters by prepending `_`.

# Testing
Begin by installing dependencies:

```
composer install
```

Then execute `composer test` in your terminal to run the automated unit tests for this code.
