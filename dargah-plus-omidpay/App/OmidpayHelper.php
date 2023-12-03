<?php

namespace DargahPlusAddon\Omidpay;

use Nasser\Dargahplus\Providers\GatewayParams;
use Nasser\Dargahplus\Providers\GateWay;
use Exception;

class OmidpayHelper extends GatewayParams implements GateWay
{
    private $apiGenerateTokenUrl =  'https://ref.sayancard.ir/ref-payment/RestServices/mts/generateTokenWithNoSign/';
    private $apiPaymentUrl = 'https://say.shaparak.ir/_ipgw_//MainTemplate/payment/';
    private $apiVerificationUrl = 'https://ref.sayancard.ir/ref-payment/RestServices/mts/verifyMerchantTrans/';
   

    private $successmessage = "Authority={Authority} And RefId={RefID}";
    private $transaction_id;

    public function gettransactionid()
    {
        return $this->transaction_id;
    }

    /**
     * @throws \Exception
     */
    public function request()
    {
        $data = array(
            'WSContext' => [
                'UserId' => $this->get('username'),
                'Password' => $this->get('password'),
            ],
            'TransType' => 'EN_GOODS',
            'ReserveNum' => $this->get('order_id'),
            'MerchantId' => $this->get('merchantid'),
            'Amount' => $this->get('amount'),
            'RedirectUrl' => $this->get('callbackurl'),
        );
        $data_string = json_encode($data);
		
		$ch = curl_init($this->apiGenerateTokenUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string)
		));
		
		$result = curl_exec($ch);
        curl_close($ch);
        
        $responseData = json_decode($result, true);

        $result = $responseData['Result'];
        if (!$this->isSucceed($result)) {
            throw new Exception($this->translateStatus($result));
        }

        // set transaction id
        $this->transaction_id = $responseData['Token'];

        // return the transaction’s id
        return $this;
    }
    public function verify()
    {
        $token = $_POST['token'];
        $refNum = $_POST['RefNum'];
        $data = [
                    'WSContext' => [
                        'UserId' => $this->get('username'),
                        'Password' => $this->get('password'),
                    ],
                    'Token' => $token,
                    'RefNum' => $refNum
                ];
        $data_string = json_encode($data);
		
		$ch = curl_init($this->apiVerificationUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string)
		));
		
		$result = curl_exec($ch);
        curl_close($ch);
        

        $body = json_decode($result);

        $result = $body->Result;
       
        if (!$this->isSucceed($result)) {
            throw new Exception($this->translateStatus($result));
        }

        $this->transaction_id = $body->RefNum;
        $successmessage = $this->successmessage;
        $successmessage = str_replace('{RefNum}',$body->RefNum,$successmessage);
        $successmessage = str_replace('{token}',$token,$successmessage);
        return $successmessage;

    }
    
    public function redirect()
    {
        
        $token = $this->transaction_id;
		echo '<form id="redirect_to_omidpay" method="post" action="'.$this->apiPaymentUrl.'" enctype="multipart/form-data" style="display:none !important;"  >
			<input type="hidden"  name="token" value="' . $token . '" />
			<input type="hidden"  name="language" value="fa" />
			<input type="submit" value="Pay"/>
			</form>
			<script language="JavaScript" type="text/javascript">
				document.getElementById("redirect_to_omidpay").submit();
			</script>';
    }
    /**
     * @param string $status
     * @return bool
     */
    private function isSucceed(string $status)
    {
        return $status == "erSucceed";
    }
    /**
     * Convert status to a readable message.
     *
     * @param $status
     *
     * @return mixed|string
     */
    private function translateStatus($status): string
    {
        $translations = [
            'erSucceed' => 'سرویس با موفقیت اجراء شد.',
            'erAAS_UseridOrPassIsRequired' => 'کد کاربری و رمز الزامی هست.',
            'erAAS_InvalidUseridOrPass' => 'کد کاربری یا رمز صحیح نمی باشد.',
            'erAAS_InvalidUserType' => 'نوع کاربر صحیح نمی‌باشد.',
            'erAAS_UserExpired' => 'کاربر منقضی شده است.',
            'erAAS_UserNotActive' => 'کاربر غیر فعال هست.',
            'erAAS_UserTemporaryInActive' => 'کاربر موقتا غیر فعال شده است.',
            'erAAS_UserSessionGenerateError' => 'خطا در تولید شناسه لاگین',
            'erAAS_UserPassMinLengthError' => 'حداقل طول رمز رعایت نشده است.',
            'erAAS_UserPassMaxLengthError' => 'حداکثر طول رمز رعایت نشده است.',
            'erAAS_InvalidUserCertificate' => 'برای کاربر فایل سرتیفکیت تعریف نشده است.',
            'erAAS_InvalidPasswordChars' => 'کاراکترهای غیر مجاز در رمز',
            'erAAS_InvalidSession' => 'شناسه لاگین معتبر نمی‌باشد ',
            'erAAS_InvalidChannelId' => 'کانال معتبر نمی‌باشد.',
            'erAAS_InvalidParam' => 'پارامترها معتبر نمی‌باشد.',
            'erAAS_NotAllowedToService' => 'کاربر مجوز سرویس را ندارد.',
            'erAAS_SessionIsExpired' => 'شناسه الگین معتبر نمی‌باشد.',
            'erAAS_InvalidData' => 'داده‌ها معتبر نمی‌باشد.',
            'erAAS_InvalidSignature' => 'امضاء دیتا درست نمی‌باشد.',
            'erAAS_InvalidToken' => 'توکن معتبر نمی‌باشد.',
            'erAAS_InvalidSourceIp' => 'آدرس آی پی معتبر نمی‌باشد.',

            'erMts_ParamIsNull' => 'پارمترهای ورودی خالی می‌باشد.',
            'erMts_UnknownError' => 'خطای ناشناخته',
            'erMts_InvalidAmount' => 'مبلغ معتبر نمی‌باشد.',
            'erMts_InvalidBillId' => 'شناسه قبض معتبر نمی‌باشد.',
            'erMts_InvalidPayId' => 'شناسه پرداخت معتبر نمی‌باشد.',
            'erMts_InvalidEmailAddLen' => 'طول ایمیل معتبر نمی‌باشد.',
            'erMts_InvalidGoodsReferenceIdLen' => 'طول شناسه خرید معتبر نمی‌باشد.',
            'erMts_InvalidMerchantGoodsReferenceIdLen' => 'طول شناسه خرید پذیرنده معتبر نمی‌باشد.',
            'erMts_InvalidMobileNo' => 'فرمت شماره موبایل معتبر نمی‌باشد.',
            'erMts_InvalidPorductId' => 'طول یا فرمت کد محصول معتبر نمی‌باشد.',
            'erMts_InvalidRedirectUrl' => 'طول یا فرمت آدرس صفحه رجوع معتبر نمی‌باشد.',
            'erMts_InvalidReferenceNum' => 'طول یا فرمت شماره رفرنس معتبر نمی‌باشد.',
            'erMts_InvalidRequestParam' => 'پارامترهای درخواست معتبر نمی‌باشد.',
            'erMts_InvalidReserveNum' => 'طول یا فرمت شماره رزرو معتبر نمی‌باشد.',
            'erMts_InvalidSessionId' => 'شناسه الگین معتبر نمی‌باشد.',
            'erMts_InvalidSignature' => 'طول یا فرمت امضاء دیتا معتبر نمی‌باشد.',
            'erMts_InvalidTerminal' => 'کد ترمینال معتبر نمی‌باشد.',
            'erMts_InvalidToken' => 'توکن معتبر نمی‌باشد.',
            'erMts_InvalidTransType' => 'نوع تراکنش معتبر نمی‌باشد.',
            'erMts_InvalidUniqueId' => 'کد یکتا معتبر نمی‌باشد.',
            'erMts_InvalidUseridOrPass' => 'رمز یا کد کاربری معتبر نمی باشد.',
            'erMts_RepeatedBillId' => 'پرداخت قبض تکراری می باشد.',
            'erMts_AASError' => 'کد کاربری و رمز الزامی هست.',
            'erMts_SCMError' => 'خطای سرور مدیریت کانال',
        ];

        $unknownError = 'خطای ناشناخته رخ داده است. در صورت کسر مبلغ از حساب حداکثر پس از 72 ساعت به حسابتان برمیگردد.';

        return array_key_exists($status, $translations) ? $translations[$status] : $unknownError;
    }
    /**
     * request rest and return the response.
     *
     * @param $uri
     * @param $data
     * @param bool $sandbox
     * @return mixed
     */
    private function restCall($uri, $data )
    {
        $base_uri = $this->get('sandbox')==1 ? self::BASESANDBOXURL : self::BASEURL;
        $base_uri = $base_uri.'rest/WebGate/';
        try {
            $client = new Client(['base_uri' => $base_uri]);
            $response = $client->request('POST', $uri, ['json' => $data]);

            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            $rawBody = is_null($response) ? '{"Status":-98,"message":"http connection error"}' : $response->getBody()->getContents();
            $body = json_decode($rawBody, true);
        }

        if (!isset($body['Status'])) {
            $body['Status'] = -99;
        }

        return $body;
    }
    public function get_params_keys()
    {  
        return array_merge(parent::get_params_keys(), [
            'username',
            'password',
            'merchantid',
            'terminalid',
            'direct_redirect'
        ]);
    }
    public function get_html_settings()
    {
        ?>
        <form id="<?php print 'dargah-plus-'.$this->get('id'); ?>">
            <input type="hidden" value="<?php print($this->model->id) ?>" name="dagahplusdargahid">
            <div class="form-group">
                <label for="omidpayusername"><?php print mdargahplus__('User name') ?></label>
                <input type="text" id="omidpayusername" name="username" value="<?php print $this->get('username') ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="omidpaypassword"><?php print mdargahplus__('Password') ?></label>
                <input type="text" id="omidpaypassword" name="password" value="<?php print $this->get('password') ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="omidpayterminalid"><?php print mdargahplus__('Terminal Id') ?></label>

                <input type="text" id="omidpayterminalid" name="terminalid" value="<?php print $this->get('terminalid') ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="omidpaymerchantid"><?php print mdargahplus__('Merchant Id') ?></label>

                <input type="text" id="omidpaymerchantid" name="merchantid" value="<?php print $this->get('merchantid') ?>" class="form-control">
            </div>
            <?php $this->get_main_fields() ?>            
            <div class="form-group">
                <label for="omidpaydirectredirect"><?php print mdargahplus__('Direct Redirect') ?></label>
                <input type="checkbox" name="direct_redirect" value="1" <?= $this->get('direct_redirect')==1? 'checked':null; ?>>
            </div>
            <div class="form-group">
                <input type="submit" value="<?php print mdargahplus__('save') ?>" class="btn btn-success save-dargah-plus-params">
            </div>
        </form>

        <?php
    }
}