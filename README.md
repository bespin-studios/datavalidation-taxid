# bespin/datavalidation-taxid
Library to verify tax ids

## List of supported countries
| Country | Name                              | Information                                                                                                      |
|---------|-----------------------------------|------------------------------------------------------------------------------------------------------------------|
| Germany | Steuerliche Identifikationsnummer | [Wikipedia - Steuerliche Identifikationsnummer](https://de.wikipedia.org/wiki/Steuerliche_Identifikationsnummer) |

## How to use
```composer require bespin/datavalidation-taxid```
```
<?php
use Bespin\DataValidation;
if (DataValidation\TaxId::verify('xxx', DataValidation\Country::Germany)) {
    echo 'xxx is a valid taxId';
} else {
    echo 'xxx is not a valid taxId';
}
```