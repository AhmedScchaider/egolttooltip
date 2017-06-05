<?php

/**
 * @package   Crypt_AES
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright MMVIII Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

/**
 * Include Crypt_Rijndael
 */
if (!class_exists('Crypt_Rijndael')) {
    include_once 'Rijndael.php';
}

/**#@+
 * @access public
 * @see Crypt_AES::encrypt()
 * @see Crypt_AES::decrypt()
 */
/**
 * Encrypt / decrypt using the Counter mode.
 *
 * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
 */
define('CRYPT_AES_MODE_CTR', CRYPT_MODE_CTR);
/**
 * Encrypt / decrypt using the Electronic Code Book mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
 */
define('CRYPT_AES_MODE_ECB', CRYPT_MODE_ECB);
/**
 * Encrypt / decrypt using the Code Book Chaining mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
 */
define('CRYPT_AES_MODE_CBC', CRYPT_MODE_CBC);
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher_feedback_.28CFB.29
 */
define('CRYPT_AES_MODE_CFB', CRYPT_MODE_CFB);
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Output_feedback_.28OFB.29
 */
define('CRYPT_AES_MODE_OFB', CRYPT_MODE_OFB);
/**#@-*/

/**#@+
 * @access private
 * @see Crypt_AES::Crypt_AES()
 */
/**
 * Toggles the internal implementation
 */
define('CRYPT_AES_MODE_INTERNAL', CRYPT_MODE_INTERNAL);
/**
 * Toggles the mcrypt implementation
 */
define('CRYPT_AES_MODE_MCRYPT', CRYPT_MODE_MCRYPT);
/**#@-*/

/**
 * Pure-PHP implementation of AES.
 *
 * @package Crypt_AES
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.1.0
 * @access  public
 */
class Crypt_AES extends Crypt_Rijndael
{
    /**
     * The namespace used by the cipher for its constants.
     *
     * @see Crypt_Base::const_namespace
     * @var String
     * @access private
     */
    var $const_namespace = 'AES';

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.
     *
     * $mode could be:
     *
     * - CRYPT_AES_MODE_ECB
     *
     * - CRYPT_AES_MODE_CBC
     *
     * - CRYPT_AES_MODE_CTR
     *
     * - CRYPT_AES_MODE_CFB
     *
     * - CRYPT_AES_MODE_OFB
     *
     * If not explictly set, CRYPT_AES_MODE_CBC will be used.
     *
     * @see Crypt_Rijndael::Crypt_Rijndael()
     * @see Crypt_Base::Crypt_Base()
     * @param optional Integer $mode
     * @access public
     */
    function Crypt_AES($mode = CRYPT_AES_MODE_CBC)
    {
        parent::Crypt_Rijndael($mode);
    }

    /**
     * Dummy function
     *
     * Since Crypt_AES extends Crypt_Rijndael, this function is, technically, available, but it doesn't do anything.
     *
     * @see Crypt_Rijndael::setBlockLength()
     * @access public
     * @param Integer $length
     */
    function setBlockLength($length)
    {
        return;
    }
}
