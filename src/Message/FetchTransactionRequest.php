<?php

namespace Omnipay\NetBanx\Message;

/**
 * NetBanx Void Request
 */
class FetchTransactionRequest extends AbstractRequest
{
    /**
     * Method
     *
     * @var string
     */
    protected $txnMode = 'cTxnLookup';

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $this->validate('transactionReference');

        $data = $this->getBaseData();
        $data['txnRequest'] = $this->getXmlString();

        return $data;
    }

    /**
     * Get XML string
     *
     * @return string
     * @throws \Exception
     */
    protected function getXmlString(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <ccTxnLookupRequestV1
                    xmlns="http://www.optimalpayments.com/creditcard/xmlschema/v1"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:schemaLocation="http://www.optimalpayments.com/creditcard/xmlschema/v1" />';

        $sxml = new \SimpleXMLElement($xml);

        $merchantAccount = $sxml->addChild('merchantAccount');

        $merchantAccount->addChild('accountNum', $this->getAccountNumber());
        $merchantAccount->addChild('storeID', $this->getStoreId());
        $merchantAccount->addChild('storePwd', $this->getStorePassword());

        $sxml->addChild('confirmationNumber', $this->getTransactionReference());
        $sxml->addChild('merchantRefNum', $this->getCustomerId());

        $startDate = $sxml->addChild('startDate');
        $this->buildDateElement(
            $startDate,
            $this->getStartYear(),
            $this->getStartMonth(),
            $this->getStartDay(),
            '0',
            '0',
            '0'
        );

        $endDate = $sxml->addChild('endDate');
        $this->buildDateElement(
            $endDate,
            $this->getEndYear(),
            $this->getEndMonth(),
            $this->getEndDay(),
            '23',
            '59',
            '59'
        );

        return $sxml->asXML();
    }

    private function buildDateElement(
        \SimpleXMLElement $dateElement,
        string $year,
        string $month,
        string $day,
        string $hour,
        string $minute,
        string $second
    ) {
        $dateElement->addChild('year', $year);
        $dateElement->addChild('month', $month);
        $dateElement->addChild('day', $day);
        $dateElement->addChild('hour', $hour);
        $dateElement->addChild('minute', $minute);
        $dateElement->addChild('second', $second);
    }

    private function setStartYear($value)
    {
        return $this->setParameter('startYear', $value);
    }

    private function getStartYear()
    {
        return $this->getParameter('startYear');
    }

    private function setStartMonth($value)
    {
        return $this->setParameter('startMonth', $value);
    }

    private function getStartMonth()
    {
        return $this->getParameter('startMonth');
    }

    private function setStartDay($value)
    {
        return $this->setParameter('startDay', $value);
    }

    private function getStartDay()
    {
        return $this->getParameter('startDay');
    }

    private function setEndYear($value)
    {
        return $this->setParameter('endYear', $value);
    }

    private function getEndYear()
    {
        return $this->getParameter('endYear');
    }

    private function setEndMonth($value)
    {
        return $this->setParameter('endMonth', $value);
    }

    private function getEndMonth()
    {
        return $this->getParameter('endMonth');
    }

    private function setEndDay($value)
    {
        return $this->setParameter('endDay', $value);
    }

    private function getEndDay()
    {
        return $this->getParameter('endDay');
    }
}
