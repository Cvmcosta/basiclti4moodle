<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// This file is part of BasicLTI4Moodle
//
// BasicLTI4Moodle is an IMS BasicLTI (Basic Learning Tools for Interoperability)
// consumer for Moodle 1.9 and Moodle 2.0. BasicLTI is a IMS Standard that allows web
// based learning tools to be easily integrated in LMS as native ones. The IMS BasicLTI
// specification is part of the IMS standard Common Cartridge 1.1 Sakai and other main LMS
// are already supporting or going to support BasicLTI. This project Implements the consumer
// for Moodle. Moodle is a Free Open source Learning Management System by Martin Dougiamas.
// BasicLTI4Moodle is a project iniciated and leaded by Ludo(Marc Alier) and Jordi Piguillem
// at the GESSI research group at UPC.
// SimpleLTI consumer for Moodle is an implementation of the early specification of LTI
// by Charles Severance (Dr Chuck) htp://dr-chuck.com , developed by Jordi Piguillem in a
// Google Summer of Code 2008 project co-mentored by Charles Severance and Marc Alier.
//
// BasicLTI4Moodle is copyright 2009 by Marc Alier Forment, Jordi Piguillem and Nikolas Galanis
// of the Universitat Politecnica de Catalunya http://www.upc.edu
// Contact info: Marc Alier Forment granludo @ gmail.com or marc.alier @ upc.edu.

/**
 * This file contains the JWK functionalities used to allow the usage of JWK keyset as a way to validate JWTs in requests.
 * This file is an adaptation of the fproject/php-jwt/JWK.php file. created by Bui Sy Nguyen <nguyenbs@gmail.com>.
 *
 * JSON Web Key implementation, based on this spec:
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-key-41
 * 
 * @package mod_lti
 * @copyright  2009 Marc Alier, Jordi Piguillem, Nikolas Galanis
 *  marc.alier@upc.edu
 * @copyright  2009 Universitat Politecnica de Catalunya http://www.upc.edu
 * @author     Marc Alier
 * @author     Jordi Piguillem
 * @author     Nikolas Galanis
 * @author     Chris Scribner
 * @copyright  2015 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \Firebase\JWT\JWT;
use UnexpectedValueException;

function parseKeySet($source) {
    $keys = [];
    if (is_string($source)) {
        $source = json_decode($source, true);
    } else if (is_object($source)) {
        if (property_exists($source, 'keys'))
            $source = (array)$source;
        else
            $source = [$source];
    }

    if (is_array($source)) {
        if (isset($source['keys']))
            $source = $source['keys'];

        foreach ($source as $k => $v) {
            if (!is_string($k)) {
                if (is_array($v) && isset($v['kid']))
                    $k = $v['kid'];
                elseif (is_object($v) && property_exists($v, 'kid'))
                    $k = $v->{'kid'};
            }
            try {
                $v = parseKey($v);
                $keys[$k] = $v;
            } catch (UnexpectedValueException $e) {
                //Do nothing
            }
        }
    }
    if (0 < count($keys)) {
        return $keys;
    }
    throw new UnexpectedValueException('Failed to parse JWK');
}

/**
 * Parse a JWK key
 * @param $source
 * @return resource|array an associative array represents the key
 */
function parseKey($source) {
    if (!is_array($source))
        $source = (array)$source;
    if (!empty($source) && isset($source['kty']) && isset($source['n']) && isset($source['e'])) {
        switch ($source['kty']) {
            case 'RSA':
                if (array_key_exists('d', $source))
                    throw new UnexpectedValueException('Failed to parse JWK: RSA private key is not supported');

                $pem = createPemFromModulusAndExponent($source['n'], $source['e']);
                $pKey = openssl_pkey_get_public($pem);
                if ($pKey !== false)
                    return $pKey;
                break;
            default:
                //Currently only RSA is supported
                break;
        }
    }

    throw new UnexpectedValueException('Failed to parse JWK');
}

/**
 *
 * Create a public key represented in PEM format from RSA modulus and exponent information
 *
 * @param string $n the RSA modulus encoded in Base64
 * @param string $e the RSA exponent encoded in Base64
 * @return string the RSA public key represented in PEM format
 */
function createPemFromModulusAndExponent($n, $e) {
    $modulus = JWT::urlsafeB64Decode($n);
    $publicExponent = JWT::urlsafeB64Decode($e);


    $components = [
        'modulus' => pack('Ca*a*', 2, encodeLength(strlen($modulus)), $modulus),
        'publicExponent' => pack('Ca*a*', 2, encodeLength(strlen($publicExponent)), $publicExponent)
    ];

    $RSAPublicKey = pack(
        'Ca*a*a*',
        48,
        encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
        $components['modulus'],
        $components['publicExponent']
    );


    // sequence(oid(1.2.840.113549.1.1.1), null)) = rsaEncryption.
    $rsaOID = pack('H*', '300d06092a864886f70d0101010500'); // hex version of MA0GCSqGSIb3DQEBAQUA
    $RSAPublicKey = chr(0) . $RSAPublicKey;
    $RSAPublicKey = chr(3) . encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;

    $RSAPublicKey = pack(
        'Ca*a*',
        48,
        encodeLength(strlen($rsaOID . $RSAPublicKey)),
        $rsaOID . $RSAPublicKey
    );

    $RSAPublicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
        chunk_split(base64_encode($RSAPublicKey), 64) .
        '-----END PUBLIC KEY-----';

    return $RSAPublicKey;
}

/**
 * DER-encode the length
 *
 * DER supports lengths up to (2**8)**127, however, we'll only support lengths up to (2**8)**4.  See
 * {@link http://itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf#p=13 X.690 paragraph 8.1.3} for more information.
 *
 * @access private
 * @param int $length
 * @return string
 */
function encodeLength($length) {
    if ($length <= 0x7F) {
        return chr($length);
    }

    $temp = ltrim(pack('N', $length), chr(0));
    return pack('Ca*', 0x80 | strlen($temp), $temp);
}

