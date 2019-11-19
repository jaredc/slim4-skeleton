---
layout: default
title: Validation
nav_order: 14
---

# Validation

Depending on the use case, different strategies are appropriate for the input validation.

## OpenAPI validation

The [league/openapi-psr7-validator](https://github.com/thephpleague/openapi-psr7-validator) 
package can validate PSR-7 messages against OpenAPI (3.0.x) specifications expressed in YAML or JSON.

The [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema) package is 
for validating JSON structures against a given schema.

## JSON schema validation

The [league/json-guard](https://json-guard.thephpleague.com/) package lets you validate JSON data 
using [json schema](https://json-schema.org/). 

## Form data validation

If you need to validate complex form data (arrays) against a specific set of rules, try 
[cakephp/validation](https://github.com/cakephp/validation) and 
[vlucas/valitron](https://github.com/vlucas/valitron).

To collect validation errors and convert a validation exception into a JSON response, 
try [selective/validation](https://github.com/selective-php/validation)

## XML validation

The [selective/xml](https://github.com/selective-php/xml) package validates XML files 
against XSD schemas.

## Assertions

The [webmozart/assert](https://github.com/webmozart/assert) and 
[beberlei/assert](https://github.com/beberlei/assert) 
packages provides assertions to validate method input/output with nice error messages.