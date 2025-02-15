<?php

namespace Omnipay\NetBanx\Message;

use Omnipay\Common\CreditCard;

/**
 * NetBanx Authorize Request
 */
class AuthorizeRequest extends AbstractRequest
{
    const MODE_AUTH = 'ccAuthorize';
    const MODE_STORED_DATA_AUTH = 'ccStoredDataAuthorize';

    /**
     * Method
     *
     * @var string
     */
    protected $txnMode;

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if ($this->getTransactionReference() || $this->getCardReference()) {
            $this->txnMode = $this->getStoredDataMode();
            $this->validate('amount');
        } else {
            $this->txnMode = $this->getBasicMode();
            $this->validate('amount', 'card');
            $this->getCard()->validate();
        }

        $data = $this->getBaseData();
        $data['txnRequest'] = $this->getXmlString();

        return $data;
    }

    /**
     * Get XML string
     *
     * @return string
     */
    protected function getXmlString()
    {
        if ($this->getTransactionReference() || $this->getCardReference()) {
            $xmlRoot = 'ccStoredDataRequestV1';
        } else {
            $xmlRoot = 'ccAuthRequestV1';
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <{$xmlRoot}
                    xmlns=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\"
                    xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
                    xsi:schemaLocation=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\" />";

        $sxml = new \SimpleXMLElement($xml);

        $merchantAccount = $sxml->addChild('merchantAccount');

        $merchantAccount->addChild('accountNum', $this->getAccountNumber());
        $merchantAccount->addChild('storeID', $this->getStoreId());
        $merchantAccount->addChild('storePwd', $this->getStorePassword());

        $sxml->addChild('merchantRefNum', $this->getCustomerId() ?: 'ref-num - ' . time());

        if ($this->getTransactionReference() || $this->getCardReference()) {
            $sxml->addChild('confirmationNumber', $this->getTransactionReference() ?: $this->getCardReference());
            $sxml->addChild('amount', $this->getAmount());
        } else {
            /** @var $card CreditCard */
            $card = $this->getCard();

            $sxml->addChild('amount', $this->getAmount());

            $cardChild = $sxml->addChild('card');

            $cardChild->addChild('cardNum', $card->getNumber());

            $cardExpiry = $cardChild->addChild('cardExpiry');
            $cardExpiry->addChild('month', $card->getExpiryDate('m'));
            $cardExpiry->addChild('year', $card->getExpiryDate('Y'));

            $cardChild->addChild('cardType', $this->translateCardType($card->getBrand()));
            $cardChild->addChild('cvdIndicator', '1');
            $cardChild->addChild('cvd', $card->getCvv());

            $billingDetails = $sxml->addChild('billingDetails');

            $billingDetails->addChild('cardPayMethod', 'WEB');

            //Mandatory
            $billingDetails->addChild('zip', $card->getBillingPostcode());

            if (!empty($card->getBillingFirstName())) {
                $billingDetails->addChild('firstName', $card->getBillingFirstName());
            }

            if (!empty($card->getBillingLastName())) {
                $billingDetails->addChild('lastName', $card->getBillingLastName());
            }

            if (!empty($card->getBillingAddress1())) {
                $billingDetails->addChild('street', $card->getBillingAddress1());
            }

            if (!empty($card->getBillingAddress2())) {
                $billingDetails->addChild('street2', $card->getBillingAddress2());
            }

            if (!empty($card->getBillingCity())) {
                $billingDetails->addChild('city', $card->getBillingCity());
            }

            if (!empty($card->getBillingState())) {
                if ($this->needState()) {
                    $billingDetails->addChild('state', $card->getBillingState());
                } else {
                    $billingDetails->addChild('region', $card->getBillingState());
                }
            }

            if (!empty($card->getBillingCountry())) {
                $billingDetails->addChild('country', $card->getBillingCountry());
            }

            if (!empty($card->getBillingPhone())) {
                $billingDetails->addChild('phone', $card->getBillingPhone());
            }

            if (!empty($card->getEmail())) {
                $billingDetails->addChild('email', $card->getEmail());
            }

            $shippingDetails = $sxml->addChild('shippingDetails');

            if (!empty($card->getShippingFirstName())) {
                $shippingDetails->addChild('firstName', $card->getShippingFirstName());
            }

            if (!empty($card->getShippingLastName())) {
                $shippingDetails->addChild('lastName', $card->getShippingLastName());
            }

            if (!empty($card->getShippingAddress1())) {
                $shippingDetails->addChild('street', $card->getShippingAddress1());
            }

            if (!empty($card->getShippingAddress2())) {
                $shippingDetails->addChild('street2', $card->getShippingAddress2());
            }


            if (!empty($card->getShippingCity())) {
                $shippingDetails->addChild('city', $card->getShippingCity());
            }

            if (!empty($card->getBillingState())) {
                if ($this->needState()) {
                    $shippingDetails->addChild('state', $card->getBillingState());
                } else {
                    $shippingDetails->addChild('region', $card->getBillingState());
                }
            }

            if (!empty($card->getShippingCountry())) {
                $shippingDetails->addChild('country', $card->getShippingCountry());
            }

            if (!empty($card->getShippingPhone())) {
                $shippingDetails->addChild('phone', $card->getShippingPhone());
            }

            if (!empty($card->getEmail())) {
                $shippingDetails->addChild('email', $card->getEmail());
            }

            //Mandatory
            $shippingDetails->addChild('zip', $card->getShippingPostcode());
        }

        return $sxml->asXML();
    }

    /**
     * Get Stored Data Mode
     *
     * @return string
     */
    protected function getStoredDataMode()
    {
        return self::MODE_STORED_DATA_AUTH;
    }

    /**
     * Get Stored Data Mode
     *
     * @return string
     */
    protected function getBasicMode()
    {
        return self::MODE_AUTH;
    }

    private function needState(): bool
    {
        return \in_array($this->getParameter('billingCountry'), ['US', 'CA']);
    }
}
