<?php

namespace Bespin\DataValidation;

use Exception;
use Throwable;

class TaxId
{
    public static function verify(string $taxId, Country $country): bool
    {
        try {
            $digits = mb_str_split(self::format($taxId, $country));
        } catch (Throwable) {
            return false;
        }
        // check the length of the tax id
        if (count($digits) !== 11) {
            return false;
        }

        //convert all digits to int
        $digits = array_map(function (string $value) {
            return (int)$value;
        }, $digits);

        //check the string contained only digits
        if ($taxId !== implode('', $digits)) {
            return false;
        }

        // first digit MUST not be 0
        if ($digits[0] === 0) {
            return false;
        }

        $checksumDigit = array_pop($digits);

        // d) Verify digit distribution rules
        if (self::verifyDigitDistribution($digits)) {
            return false;
        }

        // e) Verify checksum using the Mod 11,10 algorithm
        return self::verifyChecksum($digits, $checksumDigit);
    }

    /**
     * @throws Exception
     */
    public static function format(string $taxId, Country $country = null, Format $format = Format::machine, bool $isAlreadyMachineFormat = false): string
    {
        if ($format === Format::machine) {
            if ($isAlreadyMachineFormat) {
                return $taxId;
            }
            $taxId = preg_replace('/[^0-9]/', '', $taxId);
            if ($taxId === null) {
                throw new Exception('failed to format taxId');
            }
            return $taxId;
        } else {
            if ($country === null) {
                throw new Exception('failed to format taxId, human readable format requires a country');
            }
            if ($isAlreadyMachineFormat) {
                return ltrim(wordwrap(' '.$taxId, 3, ' ', true));
            }
            return ltrim(wordwrap(' '.self::format($taxId), 3, ' ', true));
        }
    }

    /**
     * @param array<int> $taxIdDigits
     */
    private static function verifyChecksum(array $taxIdDigits, int $checksumDigit): bool
    {
        $initialValue = 10;
        foreach ($taxIdDigits as $taxIdDigit) {
            $initialValue = ($initialValue + $taxIdDigit) % 10;
            if ($initialValue === 0) {
                $initialValue = 10;
            }
            $initialValue = ($initialValue * 2) % 11;
        }
        $calculated_checksum = (11 - $initialValue) % 10;

        return $checksumDigit === $calculated_checksum;
    }

    /**
     * @param array<int> $taxIdDigits
     */
    private static function verifyDigitDistribution(array $taxIdDigits): bool
    {
        // Count occurrences of each digit
        $digitCounts = array_count_values($taxIdDigits);

        // Ensure that the digit counts follow the rules (based on the year 2017 change, since IDs are not associated with a creation date, no need to differ)
        $twiceOrThrice = 0;

        foreach ($digitCounts as $count) {
            if ($count === 2 || $count === 3) {
                $twiceOrThrice++;
            }
        }

        // Since 2017: exactly one number must occur twice or thrice.
        // Before 2017: distribution might differ based on historical rules.
        // Here, we assume the post-2017 rule is mandatory.
        return $twiceOrThrice === 1;
    }
}