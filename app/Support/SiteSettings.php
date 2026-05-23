<?php

namespace App\Support;

class SiteSettings
{
    public const ORGANIZATION_NAME = 'organization_name';

    public const ORGANIZATION_ADDRESS = 'organization_address';

    public const ORGANIZATION_LOGO = 'organization_logo';

    public const UPI_ID = 'upi_id';

    public const UPI_QR = 'upi_qr';

    public const REGISTRATION_FEE_AMOUNT = 'registration_fee_amount';

    public const PRIVACY_POLICY_HTML = 'privacy_policy_html';

    public const SUPPORT_CONTACT_NUMBER = 'support_contact_number';

    public const SUPPORT_EMAIL = 'support_email';

    public const DEFAULTS = [
        self::ORGANIZATION_NAME => 'M/s Bhagya Laxmi',
        self::ORGANIZATION_ADDRESS => 'A-1, Patliputra Industrial Area, Patna-800013, Bihar, India',
        self::ORGANIZATION_LOGO => null,
        self::UPI_ID => null,
        self::UPI_QR => null,
        self::REGISTRATION_FEE_AMOUNT => '0',
        self::PRIVACY_POLICY_HTML => null,
        self::SUPPORT_CONTACT_NUMBER => null,
        self::SUPPORT_EMAIL => null,
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::DEFAULTS);
    }
}
