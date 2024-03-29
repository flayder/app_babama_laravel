<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class TransferReversal.
 *
 * @property string $id
 * @property string $object
 * @property int    $amount
 * @property string $balance_transaction
 * @property int    $created
 * @property string $currency
 * @property mixed  $metadata
 * @property string $transfer
 */
class TransferReversal extends ApiResource
{
    /** @return string the API URL for this StripeJS transfer reversal */
    public function instanceUrl()
    {
        $id = $this['id'];
        $transfer = $this['transfer'];
        if (!$id) {
            throw new Error\InvalidRequest('Could not determine which URL to request: '."class instance has invalid ID: $id", null);
        }
        $id = Util\Util::utf8($id);
        $transfer = Util\Util::utf8($transfer);

        $base = Transfer::classUrl();
        $transferExtn = urlencode($transfer);
        $extn = urlencode($id);

        return "$base/$transferExtn/reversals/$extn";
    }

    /**
     * @param array|string|null $opts
     *
     * @return TransferReversal the saved reversal
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }
}
