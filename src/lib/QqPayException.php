<?php
/**
 *
 * 支付API异常类
 * @author widyhu
 *
 */
class QqPayException extends Exception {
    public function errorMessage()
    {
        return $this->getMessage();
    }
}

?>