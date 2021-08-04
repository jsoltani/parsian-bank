<?php

namespace src;

use nusoap_client;
use src\jsTranslate as jsTranslate;

/**
 * jsBankParsian
 */
class jsBankParsian
{
    private static $SaleServiceAddress = "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?wsdl";
    private static $ConfirmService     = "https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?wsdl";
    private static $Encoding           = "UTF-8";
    private static $CallBackUrl        = "http://calback-page-in-here";
    private static $ParsianPin         = " parsian pin ";

    /**
     * connect to parsian bank gateway
     * @param $OrderId -> factor number
     * @param $Amount
     * @return string
     */
    public static function pay($OrderId, $Amount)
    {
        $client = new nusoap_client(self::$SaleServiceAddress, 'wsdl');
        $client->soap_defencoding = self::$Encoding;
        $client->decode_utf8 = FALSE;

        $err = $client->getError();
        if ($err) {
            return self::response(-1, $err, []);
        }

        $parameters = [
            'LoginAccount' => self::$ParsianPin,
            'Amount' => $Amount,
            'OrderId' => $OrderId,
            'CallBackUrl' => self::$CallBackUrl
        ];

        $result = $client->call('SalePaymentRequest', ['requestData' => $parameters]);
        $err = $client->getError();
        if ($err) {
            return self::response(-1, $err, []);
        } else {
            $Token = $result['SalePaymentRequestResult']['Token'];
            $Status = $result['SalePaymentRequestResult']['Status'];
            $Message = $result['SalePaymentRequestResult']['Message'];

            // insert into database

            return self::response($Status, $Message,[
                'Status' => $Status,
                'Token' => $Token,
                'Message' => $Message
            ]);
        }

    }

    /**
     * call after bank callback for verify
     * @param $RRN -> reference number
     * @param $Token
     * @param $Status
     * @return string
     */
    public static function callback($RRN, $Token, $Status)
    {
        if ($RRN > 0 and $Status == 0) {

            $client = new nusoap_client(self::$ConfirmService, 'wsdl');
            $client->soap_defencoding = self::$Encoding;
            $client->decode_utf8 = FALSE;
            $err = $client->getError();
            if ($err) {
                return self::response(-1, $err, []);
            }

            $parameters = [
                'LoginAccount' => self::$ParsianPin,
                'Token' => $Token
            ];

            $result = $client->call('ConfirmPayment', ['requestData' => $parameters]);
            if ($client->fault) {
                $err = $client->getError();
                return self::response(-1, $err, []);
            } else {

                // update database

                return self::response($Status, self::errors($Status), [
                    'Status' => $result['ConfirmPaymentResult']['Status'] ?? -123456789,
                    'Token' => $result['ConfirmPaymentResult']['Token'],
                    'Message' => $result['ConfirmPaymentResult']['Message'] ?? self::errors($Status),
                    'RRN' => $result['ConfirmPaymentResult']['RRN'],
                    'CardNumberMasked' => $result['ConfirmPaymentResult']['CardNumberMasked']
                ]);
            }
        } else {

            // update database

            return self::response($Status, self::errors($Status), [
                'Status' => $Status,
                'Token' => $Token,
                'Message' => self::errors($Status),
                'RRN' => $RRN,
                'CardNumberMasked' => ''
            ]);
        }

    }

    /**
     * set callback page
     * @param string $callbackPage
     */
    public static function setCallBackUrl($callbackPage = ''){
        self::$CallBackUrl = $callbackPage;
    }

    /**
     * set parsian pin for access to account
     * @param string $parsianPin
     */
    public static function setParsianPin($parsianPin = ''){
        self::$ParsianPin = $parsianPin;
    }

    /**
     * generate output
     * @param int $Status
     * @param string $Message
     * @param array $Items
     * @return string
     */
    private static function response($Status = -1, $Message = '', $Items = []){
        $data = [
            'responseCode' => $Status,
            'responseMessage' => $Message,
            'responseItems' => $Items
        ];
        return json_encode($data);
    }

    /**
     * bank error codes
     * @param int $errCode
     * @return string
     */
    public static function errors($errCode = -1)
    {
        switch ($errCode) {
            case -1 :
                $err = jsTranslate::translate('serverError');
                break;
            case -2 :
                $err = jsTranslate::translate('factorNumberNotRegistered');
                break;
            case -3 :
                $err = jsTranslate::translate('factorNumberHasAlreadyBeenRegistered');
                break;
            case -32768 :
                $err = jsTranslate::translate('unknownErrorOccurred');
                break;
            case -1540 :
                $err = jsTranslate::translate('confirmationTransactionFailed');
                break;
            case -1528 :
                $err = jsTranslate::translate('paymentInformationWasNotFound');
                break;
            case -1505 :
                $err = jsTranslate::translate('transactionVerificationWasDoneByTheReceiver');
                break;
            case -138 :
                $err = jsTranslate::translate('userPaymentCanceled');
                break;
            case -132 :
                $err = jsTranslate::translate('transactionAmountIsBelowTheLimit');
                break;
            case -131 :
                $err = jsTranslate::translate('tokenInvalid');
                break;
            case -128 :
                $err = jsTranslate::translate('ipAddressInvalid');
                break;
            case -127 :
                $err = jsTranslate::translate('invalidUrl');
                break;
            case -126 :
                $err = jsTranslate::translate('acceptanceCodeIsNotValid');
                break;
            case 0 :
                $err = jsTranslate::translate('paymentWasSuccessful');
                break;
            case 1 :
                $err = jsTranslate::translate('cardIssuerDeclinedToCompleteTheTransaction');
                break;
            case 3:
                $err = jsTranslate::translate('invalidStoreAcceptor');
                break;
            case 5:
                $err = jsTranslate::translate('transactionWasDiscarded');
                break;
            case 6:
                $err = jsTranslate::translate('unknownErrors');
                break;
            case 12:
                $err = jsTranslate::translate('transactionInvalid');
                break;
            case 13:
                $err = jsTranslate::translate('transactionAmountIsIncorrect');
                break;
            case 14:
                $err = jsTranslate::translate('cardNumberInvalid');
                break;
            case 17:
                $err = jsTranslate::translate('customerRequestDeleted');
                break;
            case 33:
                $err = jsTranslate::translate('expiredExpirationDate');
                break;
            case 41:
                $err = jsTranslate::translate('cardIsMissing');
                break;
            case 43:
                $err = jsTranslate::translate('cardIsStolen');
                break;
            case 51:
                $err = jsTranslate::translate('inventoryIsNotEnough');
                break;
            case 54:
                $err = jsTranslate::translate('expiredExpirationDate');
                break;
            case 55:
                $err = jsTranslate::translate('passwordIsInvalid');
                break;
            case 56:
                $err = jsTranslate::translate('cardIsInvalid');
                break;
            case 62:
                $err = jsTranslate::translate('cardIsLimited');
                break;
            case 78:
                $err = jsTranslate::translate('cardNotEnable');
                break;
            default:
                $err = jsTranslate::translate('unknownError');
                break;
        }

        return $err;
    }

}