<?php

namespace src;

use nusoap_client;

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

    private static function response($Status = -1, $Message = '', $Items = []){
        $data = [
            'responseCode' => $Status,
            'responseMessage' => $Message,
            'responseItems' => $Items
        ];
        return json_encode($data);
    }

    private static function errors($errCode = -1)
    {
        switch ($errCode) {
            case -1 :
                $err = "خطای سرور";
                break;
            case -2 :
                $err = "شماره فاکتور ثبت نشده است";
                break;
            case -3 :
                $err = "شماره فاکتور قبلا ثبت شده است";
                break;
            case -32768 :
                $err = "خطای ناشناخته رخ داده است";
                break;
            case -1540 :
                $err = "تایید تراکنش ناموفق می باشد";
                break;
            case -1528 :
                $err = "اطلاعات پرداخت یافت نشد";
                break;
            case -1505 :
                $err = "تایید تراکنش توسط پذیرنده انجام شد";
                break;
            case -138 :
                $err = "عملیات پرداخت توسط کاربر لغو شد";
                break;
            case -132 :
                $err = "مبلغ تراکنش کمتر از حد مجاز است";
                break;
            case -131 :
                $err = "توکن نامعتبر است";
                break;
            case -128 :
                $err = "قالب آدرس IP معتبر نمی باشد";
                break;
            case -127 :
                $err = "آدرس اینترنتی نامعتبر می باشد";
                break;
            case -126 :
                $err = "کدشناسایی پذیرنده معتبر نمی باشد";
                break;
            case 0 :
                $err = "پرداخت با موفقیت انجام شد";
                break;
            case 1 :
                $err = "صادرکننده کارت از انجام تراکنش صرف نظر کرد";
                break;
            case 3:
                $err = "پذیرنده فروشگاهی نامعتبر می باشد";
                break;
            case 5:
                $err = "از انجام تراکنش صرف نظر شد";
                break;
            case 6:
                $err = "بروز خطایی ناشناخته";
                break;
            case 12:
                $err = "تراکنش نامعتبر است";
                break;
            case 13:
                $err = "مبلغ تراکنش نادرست است";
                break;
            case 14:
                $err = "شماره کارت ارسالی نامعتبراست";
                break;
            case 17:
                $err = "مشتری درخواست کننده حذف شده";
                break;
            case 33:
                $err = "تاریخ انقضای کارت سپری شده است";
                break;
            case 41:
                $err = "کارت مفقودی می باشد";
                break;
            case 43:
                $err = "کارت مسروقه می باشد";
                break;
            case 51:
                $err = "موجودی کافی نمی باشد";
                break;
            case 54:
                $err = "تاریخ انقضای کارت سپری شده است";
                break;
            case 55:
                $err = "رمز کارت نامعتبر است";
                break;
            case 56:
                $err = "کارت نامعتبر است";
                break;
            case 62:
                $err = "کارت محدود شده";
                break;
            case 78:
                $err = "کارت فعال نیست";
                break;
            default:
                $err = "خطای نا مشخص";
                break;
        }

        return $err;
    }

}