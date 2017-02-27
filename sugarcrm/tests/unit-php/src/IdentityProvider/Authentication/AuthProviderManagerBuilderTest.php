<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder
 */
class AuthProviderManagerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::buildAuthProviders
     */
    public function testBuildAuthProviders()
    {
        $data = $this->getConfig();
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('get')
            ->with($this->equalTo('passwordHash'), $this->isEmpty())
            ->willReturn([]);
        $config->expects($this->once())
            ->method('getSAMLConfig')
            ->willReturn($data['auth']['saml']);
        $config->expects($this->once())
            ->method('getLdapConfig')
            ->willReturn($data['auth']['ldap']);
        $manager = (new AuthProviderManagerBuilder($config))->buildAuthProviders();
        $this->assertInstanceOf(AuthenticationProviderManager::class, $manager);
    }

    /**
     * @coversNothing
     * @return mixed
     */
    protected function getConfig()
    {
        $sugar_config['auth'] = [];
        $sugar_config['auth']['ldap'] = [
            'adapter_config' => [
                'host' => '127.0.0.1',
                'port' => 389,
            ],
            'adapter_connection_protocol_version' => 3,
            'baseDn' => '',
            'searchDn' => '',
            'searchPassword' => '',
            'dnString' => '',
            'uidKey' => 'userPrincipalName',
            'filter' => '({uid_key}={username})',
        ];

        $sugar_config['auth']['saml']['Okta'] = [
            'strict' => false,
            'debug' => true,
            'sp' => array (
                'entityId' => 'http://localhost:8000/saml/metadata',
                'assertionConsumerService' => array (
                    'url' => 'http://localhost:8000/saml/acs',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ),
                'singleLogoutService' => array (
                    'url' => 'http://localhost:8000/saml/logout',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => '-----BEGIN CERTIFICATE-----
                    MIIDdTCCAl2gAwIBAgIJAMoIJg5+hQELMA0GCSqGSIb3DQEBCwUAMFExCzAJBgNV
                    BAYTAkJZMQ4wDAYDVQQIDAVNaW5zazEOMAwGA1UEBwwFTWluc2sxETAPBgNVBAoM
                    CFN1Z2FyQ1JNMQ8wDQYDVQQDDAZBbmRyZXcwHhcNMTYwODIzMTEyMDU0WhcNMTcw
                    ODIzMTEyMDU0WjBRMQswCQYDVQQGEwJCWTEOMAwGA1UECAwFTWluc2sxDjAMBgNV
                    BAcMBU1pbnNrMREwDwYDVQQKDAhTdWdhckNSTTEPMA0GA1UEAwwGQW5kcmV3MIIB
                    IjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvhAn1TlTyUsn6JUvSnAYcT/5
                    8hLRKz0J1SbU0xZ/vSQZrVYLlAbzF69AlS+/bfa5DUnIWC7Wte+JXSbzQ2P9Tx/m
                    bnSVUtJiRUdsPoj3bUFQbhGaT+LbPf8TcEMGpsc8JsAftSksC4wS1MuBqlpD4eib
                    jUF8kjbB6i2c34zrqWX1mCJCFSae9YEocH9YW79dfYjcjK1T2N5tV0LVWgiU/V1g
                    tFx98v/ibFBPO75MOH3gRmFE1a9fX0uD/w0bDlV1HE0F0+1hCNrbCaw/4uex5SWh
                    OoaNTS0kueH3AcXtY1ju4WBlmloIenRJVQh/WgKSteKTvzLwrRkuxt061wHzVwID
                    AQABo1AwTjAdBgNVHQ4EFgQUM+DmVDOGTb/l6F8EWgc6gdJYjYgwHwYDVR0jBBgw
                    FoAUM+DmVDOGTb/l6F8EWgc6gdJYjYgwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0B
                    AQsFAAOCAQEAeVdxprEsLUT3ZHYwox1XzKwDk3UPu67jVtjrjYXLr/qBqWVbDrAd
                    VRimX9fI6pr9MFBzgVbMfZ6VkEwgfNgJnrKja4db/4fk6qapPLf0FmOhDRjCweU6
                    Tz4pQ/QMeNBWbeK6Ekjqyz5mCxrbDmwNb/tuD6MzEJMtpwNXNcU1X68rI6wY9Cdk
                    8BWYNT3MQvf/NjoR6Feqk/qdgmEESZM/QLhj6vb2LxdfHniBGBMFpuwnnzScNHc5
                    NzYr9XzUJXcfVRsrKaKa1nLl21zeVKqNqf8SwFbJV9a6/UYRnnS84hkawIP6WOLH
                    DHdqN4yQ/I7etFrMgWCeypFLCSN+46GHZw==
                    -----END CERTIFICATE-----',
                'privateKey' => '-----BEGIN PRIVATE KEY-----
                    MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQC+ECfVOVPJSyfo
                    lS9KcBhxP/nyEtErPQnVJtTTFn+9JBmtVguUBvMXr0CVL79t9rkNSchYLta174ld
                    JvNDY/1PH+ZudJVS0mJFR2w+iPdtQVBuEZpP4ts9/xNwQwamxzwmwB+1KSwLjBLU
                    y4GqWkPh6JuNQXySNsHqLZzfjOupZfWYIkIVJp71gShwf1hbv119iNyMrVPY3m1X
                    QtVaCJT9XWC0XH3y/+JsUE87vkw4feBGYUTVr19fS4P/DRsOVXUcTQXT7WEI2tsJ
                    rD/i57HlJaE6ho1NLSS54fcBxe1jWO7hYGWaWgh6dElVCH9aApK14pO/MvCtGS7G
                    3TrXAfNXAgMBAAECggEBAKrvIrPsnAM0eY7+5QpAaGsqC6P/8mi9u6MdCllSKc40
                    sncnJMCbw3NwpVfHGpZOR73AttNARNBZvyOtDSl1uvK3kOmUJlvXZJREGQDg9A4p
                    qKllYXApad6HErdrQIcsNlfvgFTQ05ELCECjSlmoVtbM+WEAHYXug1YWcbjIJ4Yv
                    4jcQwy4hesCW1QfhrvNjNCgURcPjZza8QhrtmCOiuQdJNUq9qWTeZnHnTqsF2IU4
                    K6XNNdLxf2xZ7F+u4hVxcemjti7Nqd5sFkS6jHoKA6KFkdUfepAR5NwE9bZuLHz8
                    ls3g0rVrzMLdhXPI+8zerWzXJbwI6v/xaWxA+XCXwFECgYEA+EOYdd00iZ+owkU/
                    8ST5l104qW272igQl6ATsnRNU0s0N7Opoxdi2WWlc1rDI82HTuO4rlQIf6cfLLpP
                    ssjC1FAihhVgMy3NWxnFByUgZDMVuMsRaIvZ2336Bg4spgjdf9pl5SIhmZS5CuZd
                    U9ntt43PuW3H/zYtg49w9nrQe3kCgYEAw/xKaQADhbW9M+00WaLrs6uSTSvj1jzV
                    zja9gGdjmMMhDq6xSDQBXnvobE5YBA4idBXegNUkaFA38W/EoZtKVPeOtp2d4FTO
                    F8a5lMGoyehz5gJAghdDxr9xxyDC2nhLXxFej5Mblp92v4yj2U/rq99yRwxO6Uco
                    2gamZSU7YU8CgYBY6lzAWelnIPegHI06ILQDsi+I/vQ4vgCzTXHAiEbpfhXFnWM0
                    NjwBAJaxKeCaAhJj/ss2JIKmtYRE0LWaoqykvc6flyhNLCpQZnpahMGFIYa2GISz
                    nOL56bSSVqFHFgW+tMmptv+xscJUVQ036uVoyDGNh/QJQ64pYEZlALeKgQKBgQDB
                    8vJQZssVj3zl3mBoNGq9K5Vk+YJHiXyszk9KuwY9Lx2PwiF/KrgQIN8qD33axYIj
                    D2FabZPSB1DVhZ45r8wnubVp0yFh14r8zJTrOZsn9Pp9LM1Z8FwKW3rlbO5n9ZPh
                    SPcjbplmvfhuJ2gerpCzTjVxSiTthpZO7TXN8sKI0QKBgQCJV1u1K1t+SbosI0L9
                    Ab7UTSjGBfyN5MJk8/S+XFBfR8BEKNF7jLoJRlIGd4gBrCpCRmOfl/0y/LnJkcPn
                    5mpN0HdfQ0UG48FvxovA+mlu88mXn9BGcGkl0wCLOqweLoty8x11bj0F/HzwML+I
                    Oc2LpfBzb+d2XXEU54BlXH5J5A==
                    -----END PRIVATE KEY-----
                    ',
            ),

            'idp' => array (
                'entityId' => 'http://www.okta.com/exk7y9w6b9H1jG46H0h7',
                'singleSignOnService' => array (
                    'url' => 'https://dev-178368.oktapreview.com/app/sugarcrmdev280437_testidp_1/exk7y9w6b9H1jG46H0h7/sso/saml',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'singleLogoutService' => array (
                    'url' => 'https://dev-178368.oktapreview.com/app/sugarcrmdev280437_testidp_1/exk7y9w6b9H1jG46H0h7/slo/saml',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'x509cert' => '-----BEGIN CERTIFICATE-----
                      MIIDpDCCAoygAwIBAgIGAVad+pKSMA0GCSqGSIb3DQEBBQUAMIGSMQswCQYDVQQGEwJVUzETMBEG
                      A1UECAwKQ2FsaWZvcm5pYTEWMBQGA1UEBwwNU2FuIEZyYW5jaXNjbzENMAsGA1UECgwET2t0YTEU
                      MBIGA1UECwwLU1NPUHJvdmlkZXIxEzARBgNVBAMMCmRldi0xNzgzNjgxHDAaBgkqhkiG9w0BCQEW
                      DWluZm9Ab2t0YS5jb20wHhcNMTYwODE4MTQwNjM5WhcNMjYwODE4MTQwNzM5WjCBkjELMAkGA1UE
                      BhMCVVMxEzARBgNVBAgMCkNhbGlmb3JuaWExFjAUBgNVBAcMDVNhbiBGcmFuY2lzY28xDTALBgNV
                      BAoMBE9rdGExFDASBgNVBAsMC1NTT1Byb3ZpZGVyMRMwEQYDVQQDDApkZXYtMTc4MzY4MRwwGgYJ
                      KoZIhvcNAQkBFg1pbmZvQG9rdGEuY29tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA
                      mEm1hUEtb/eWv7EwFAX9rj1hgUC1MxopGNIVHxowc36IThleVky+2ACKxeuY+G6M+UAPFgBP/ktF
                      E/uwF3Ed9dAcdmzQ3q+Xm0GxlESkCb1AvJQnMh+UAyOlBIEI1KPGRI5y/X9TiCPRvsl57tS2Kw7/
                      UnxfElTuv2ShKjt6R9guFx1SPL8RAPpFnk6rW9/Y0GoNWjeblRD6R03vjxQz86quLHzLXdoc3igN
                      Hq0nNk/HRnBxRMTCxhdv54Ti7n5LZtaTBSbCkjAxfbbd5N3D/Bq7kJ3EJdxq/OfDEJR9oebaCysH
                      BuGkhegZco+kKEeLwJZf0DCH+AAmh8PjXsnB0QIDAQABMA0GCSqGSIb3DQEBBQUAA4IBAQB+IN2b
                      dqlGG5PZLuXTT33qkTR7aNTRlN4K+wy5KC0SGtm0IiGIR0rCSMtfHVyOOy1hodAv6DgjJ4Ejt4i9
                      rJZXTksDj57kP6cSG6ngJ9KbYHcoJN6PgK5rfWF1imHGuegdDADahxfMrgISeKz9JnkYdG0i2rBo
                      7B7CsMknnRWQL1V4deV3Db8qwrrWmJv2LvsrNUzYeh/9JPbLU2CWnp+j0HEH664D0ZFwhzwUX+QN
                      0s7jNKhU4VXLkdBe6XcCX5pFYW3H4vKz2LSrCpHmuoidJqs4RaJotoTa4px5uImOn9kbIAqbHHUb
                      F2XNRGdksB0l7arTUgTTe+1RsZeshp/L
                      -----END CERTIFICATE-----',
            ),
        ];

        $sugar_config['auth']['saml']['OneLogin'] = [
            'strict' => false,
            'debug' => true,
            'sp' => [
                'entityId' => 'idpdev',
                'assertionConsumerService' => array (
                    'url' => 'http://localhost:8000/saml/acs',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ),
                'singleLogoutService' => array (
                    'url' => 'http://localhost:8000/saml/logout',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => '-----BEGIN CERTIFICATE-----
                    MIIDdTCCAl2gAwIBAgIJAMoIJg5+hQELMA0GCSqGSIb3DQEBCwUAMFExCzAJBgNV
                    BAYTAkJZMQ4wDAYDVQQIDAVNaW5zazEOMAwGA1UEBwwFTWluc2sxETAPBgNVBAoM
                    CFN1Z2FyQ1JNMQ8wDQYDVQQDDAZBbmRyZXcwHhcNMTYwODIzMTEyMDU0WhcNMTcw
                    ODIzMTEyMDU0WjBRMQswCQYDVQQGEwJCWTEOMAwGA1UECAwFTWluc2sxDjAMBgNV
                    BAcMBU1pbnNrMREwDwYDVQQKDAhTdWdhckNSTTEPMA0GA1UEAwwGQW5kcmV3MIIB
                    IjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvhAn1TlTyUsn6JUvSnAYcT/5
                    8hLRKz0J1SbU0xZ/vSQZrVYLlAbzF69AlS+/bfa5DUnIWC7Wte+JXSbzQ2P9Tx/m
                    bnSVUtJiRUdsPoj3bUFQbhGaT+LbPf8TcEMGpsc8JsAftSksC4wS1MuBqlpD4eib
                    jUF8kjbB6i2c34zrqWX1mCJCFSae9YEocH9YW79dfYjcjK1T2N5tV0LVWgiU/V1g
                    tFx98v/ibFBPO75MOH3gRmFE1a9fX0uD/w0bDlV1HE0F0+1hCNrbCaw/4uex5SWh
                    OoaNTS0kueH3AcXtY1ju4WBlmloIenRJVQh/WgKSteKTvzLwrRkuxt061wHzVwID
                    AQABo1AwTjAdBgNVHQ4EFgQUM+DmVDOGTb/l6F8EWgc6gdJYjYgwHwYDVR0jBBgw
                    FoAUM+DmVDOGTb/l6F8EWgc6gdJYjYgwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0B
                    AQsFAAOCAQEAeVdxprEsLUT3ZHYwox1XzKwDk3UPu67jVtjrjYXLr/qBqWVbDrAd
                    VRimX9fI6pr9MFBzgVbMfZ6VkEwgfNgJnrKja4db/4fk6qapPLf0FmOhDRjCweU6
                    Tz4pQ/QMeNBWbeK6Ekjqyz5mCxrbDmwNb/tuD6MzEJMtpwNXNcU1X68rI6wY9Cdk
                    8BWYNT3MQvf/NjoR6Feqk/qdgmEESZM/QLhj6vb2LxdfHniBGBMFpuwnnzScNHc5
                    NzYr9XzUJXcfVRsrKaKa1nLl21zeVKqNqf8SwFbJV9a6/UYRnnS84hkawIP6WOLH
                    DHdqN4yQ/I7etFrMgWCeypFLCSN+46GHZw==
                    -----END CERTIFICATE-----',
                'privateKey' => '-----BEGIN PRIVATE KEY-----
                    MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQC+ECfVOVPJSyfo
                    lS9KcBhxP/nyEtErPQnVJtTTFn+9JBmtVguUBvMXr0CVL79t9rkNSchYLta174ld
                    JvNDY/1PH+ZudJVS0mJFR2w+iPdtQVBuEZpP4ts9/xNwQwamxzwmwB+1KSwLjBLU
                    y4GqWkPh6JuNQXySNsHqLZzfjOupZfWYIkIVJp71gShwf1hbv119iNyMrVPY3m1X
                    QtVaCJT9XWC0XH3y/+JsUE87vkw4feBGYUTVr19fS4P/DRsOVXUcTQXT7WEI2tsJ
                    rD/i57HlJaE6ho1NLSS54fcBxe1jWO7hYGWaWgh6dElVCH9aApK14pO/MvCtGS7G
                    3TrXAfNXAgMBAAECggEBAKrvIrPsnAM0eY7+5QpAaGsqC6P/8mi9u6MdCllSKc40
                    sncnJMCbw3NwpVfHGpZOR73AttNARNBZvyOtDSl1uvK3kOmUJlvXZJREGQDg9A4p
                    qKllYXApad6HErdrQIcsNlfvgFTQ05ELCECjSlmoVtbM+WEAHYXug1YWcbjIJ4Yv
                    4jcQwy4hesCW1QfhrvNjNCgURcPjZza8QhrtmCOiuQdJNUq9qWTeZnHnTqsF2IU4
                    K6XNNdLxf2xZ7F+u4hVxcemjti7Nqd5sFkS6jHoKA6KFkdUfepAR5NwE9bZuLHz8
                    ls3g0rVrzMLdhXPI+8zerWzXJbwI6v/xaWxA+XCXwFECgYEA+EOYdd00iZ+owkU/
                    8ST5l104qW272igQl6ATsnRNU0s0N7Opoxdi2WWlc1rDI82HTuO4rlQIf6cfLLpP
                    ssjC1FAihhVgMy3NWxnFByUgZDMVuMsRaIvZ2336Bg4spgjdf9pl5SIhmZS5CuZd
                    U9ntt43PuW3H/zYtg49w9nrQe3kCgYEAw/xKaQADhbW9M+00WaLrs6uSTSvj1jzV
                    zja9gGdjmMMhDq6xSDQBXnvobE5YBA4idBXegNUkaFA38W/EoZtKVPeOtp2d4FTO
                    F8a5lMGoyehz5gJAghdDxr9xxyDC2nhLXxFej5Mblp92v4yj2U/rq99yRwxO6Uco
                    2gamZSU7YU8CgYBY6lzAWelnIPegHI06ILQDsi+I/vQ4vgCzTXHAiEbpfhXFnWM0
                    NjwBAJaxKeCaAhJj/ss2JIKmtYRE0LWaoqykvc6flyhNLCpQZnpahMGFIYa2GISz
                    nOL56bSSVqFHFgW+tMmptv+xscJUVQ036uVoyDGNh/QJQ64pYEZlALeKgQKBgQDB
                    8vJQZssVj3zl3mBoNGq9K5Vk+YJHiXyszk9KuwY9Lx2PwiF/KrgQIN8qD33axYIj
                    D2FabZPSB1DVhZ45r8wnubVp0yFh14r8zJTrOZsn9Pp9LM1Z8FwKW3rlbO5n9ZPh
                    SPcjbplmvfhuJ2gerpCzTjVxSiTthpZO7TXN8sKI0QKBgQCJV1u1K1t+SbosI0L9
                    Ab7UTSjGBfyN5MJk8/S+XFBfR8BEKNF7jLoJRlIGd4gBrCpCRmOfl/0y/LnJkcPn
                    5mpN0HdfQ0UG48FvxovA+mlu88mXn9BGcGkl0wCLOqweLoty8x11bj0F/HzwML+I
                    Oc2LpfBzb+d2XXEU54BlXH5J5A==
                    -----END PRIVATE KEY-----
                    ',
            ],

            'idp' => [
                'entityId' => 'https://app.onelogin.com/saml/metadata/619509',
                'singleSignOnService' => array (
                    'url' => 'https://ddolbik-dev.onelogin.com/trust/saml2/http-post/sso/619509',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'singleLogoutService' => array (
                    'url' => 'https://ddolbik-dev.onelogin.com/trust/saml2/http-redirect/slo/619509',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'x509cert' => '-----BEGIN CERTIFICATE-----
                       MIIEFzCCAv+gAwIBAgIUd1QokbJ6e6pqcOLbJF/FuLEnAEkwDQYJKoZIhvcNAQEF
                       BQAwWDELMAkGA1UEBhMCVVMxETAPBgNVBAoMCFN1Z2FyQ1JNMRUwEwYDVQQLDAxP
                       bmVMb2dpbiBJZFAxHzAdBgNVBAMMFk9uZUxvZ2luIEFjY291bnQgODc4NzIwHhcN
                       MTYwNzE4MDkxNzU4WhcNMjEwNzE5MDkxNzU4WjBYMQswCQYDVQQGEwJVUzERMA8G
                       A1UECgwIU3VnYXJDUk0xFTATBgNVBAsMDE9uZUxvZ2luIElkUDEfMB0GA1UEAwwW
                       T25lTG9naW4gQWNjb3VudCA4Nzg3MjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC
                       AQoCggEBANmImoAkbCbGyDrSXqRrqW4TRu4N62uRPJI1yDtQWVRI7r1ZUbkD0Mw/
                       OfviiPpfwGPQhlywLcNKhuJKHCewjVXJiNZ/F6+HEcSXZpdX8yc9R3H+8oKHhiL+
                       c9/sp+UntqE3Xcg7WqRz8HLEAdD0y//WNhuHvlU3K+7VnJEZOshfkG9a+9mzbQU/
                       mPV9SU6JmTuxsom89rEzLCBax5yBLsMZtFcZs7s1I4169VQen0I2TaFFipE0h4zN
                       +2SceLKOsCleQ03q3ysetWSbnuegFKwkxK0YjCQqOfbw9LmVTivwCt0o2VSv11m1
                       KOsE6AmfQyiHtcYPIAB9J/7bRfjSFUsCAwEAAaOB2DCB1TAMBgNVHRMBAf8EAjAA
                       MB0GA1UdDgQWBBTcEqvrwjjSUu4AwwP1NuZC9aTNQjCBlQYDVR0jBIGNMIGKgBTc
                       EqvrwjjSUu4AwwP1NuZC9aTNQqFcpFowWDELMAkGA1UEBhMCVVMxETAPBgNVBAoM
                       CFN1Z2FyQ1JNMRUwEwYDVQQLDAxPbmVMb2dpbiBJZFAxHzAdBgNVBAMMFk9uZUxv
                       Z2luIEFjY291bnQgODc4NzKCFHdUKJGyenuqanDi2yRfxbixJwBJMA4GA1UdDwEB
                       /wQEAwIHgDANBgkqhkiG9w0BAQUFAAOCAQEAyy8jw4v30zvK6J0ROZrGnPVoHfNc
                       4gb0FbbsRQorbvT4tJhIeeXK2ICi/HVODMPX85arrD/oKmu91Fnf5Qa/zA/8mcQ/
                       Q64MFyPycOCJus37Usq+1XUEpYq90h64j9tBTrY6nxxeLR86HgbbYIZfAq/iTMEs
                       p8H+6sna0ClDGCPlPZnhyt5Wxbx2EI+RtKAXHGlOU5H/+p2eeQ/UnZphhocQC3Jc
                       du5wPFH3l2lTKukm1iiGNIUZfJmq/LIOxL9hbfU+uozuagCXW8BYAsmnaAFc7+n/
                       deYkurQO8dTcU7AzG3AkQXjGDAoo7zar+s1pNjZpkAHld1funS8FM+sK3w==
                       -----END CERTIFICATE-----',
            ],
        ];

        $sugar_config['auth']['saml']['ADFS'] = [
            'strict' => false,
            'debug' => true,
            'sp' => [
                'entityId' => '6a227274-ade1-4529-9163-2cf8c4ed8ae2',
                'assertionConsumerService' => array (
                    'url' => 'http://localhost:8000/saml/acs',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ),
                'singleLogoutService' => array (
                    'url' => 'http://localhost:8000/saml/logout',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => '-----BEGIN CERTIFICATE-----
                    MIIDdTCCAl2gAwIBAgIJAMoIJg5+hQELMA0GCSqGSIb3DQEBCwUAMFExCzAJBgNV
                    BAYTAkJZMQ4wDAYDVQQIDAVNaW5zazEOMAwGA1UEBwwFTWluc2sxETAPBgNVBAoM
                    CFN1Z2FyQ1JNMQ8wDQYDVQQDDAZBbmRyZXcwHhcNMTYwODIzMTEyMDU0WhcNMTcw
                    ODIzMTEyMDU0WjBRMQswCQYDVQQGEwJCWTEOMAwGA1UECAwFTWluc2sxDjAMBgNV
                    BAcMBU1pbnNrMREwDwYDVQQKDAhTdWdhckNSTTEPMA0GA1UEAwwGQW5kcmV3MIIB
                    IjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvhAn1TlTyUsn6JUvSnAYcT/5
                    8hLRKz0J1SbU0xZ/vSQZrVYLlAbzF69AlS+/bfa5DUnIWC7Wte+JXSbzQ2P9Tx/m
                    bnSVUtJiRUdsPoj3bUFQbhGaT+LbPf8TcEMGpsc8JsAftSksC4wS1MuBqlpD4eib
                    jUF8kjbB6i2c34zrqWX1mCJCFSae9YEocH9YW79dfYjcjK1T2N5tV0LVWgiU/V1g
                    tFx98v/ibFBPO75MOH3gRmFE1a9fX0uD/w0bDlV1HE0F0+1hCNrbCaw/4uex5SWh
                    OoaNTS0kueH3AcXtY1ju4WBlmloIenRJVQh/WgKSteKTvzLwrRkuxt061wHzVwID
                    AQABo1AwTjAdBgNVHQ4EFgQUM+DmVDOGTb/l6F8EWgc6gdJYjYgwHwYDVR0jBBgw
                    FoAUM+DmVDOGTb/l6F8EWgc6gdJYjYgwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0B
                    AQsFAAOCAQEAeVdxprEsLUT3ZHYwox1XzKwDk3UPu67jVtjrjYXLr/qBqWVbDrAd
                    VRimX9fI6pr9MFBzgVbMfZ6VkEwgfNgJnrKja4db/4fk6qapPLf0FmOhDRjCweU6
                    Tz4pQ/QMeNBWbeK6Ekjqyz5mCxrbDmwNb/tuD6MzEJMtpwNXNcU1X68rI6wY9Cdk
                    8BWYNT3MQvf/NjoR6Feqk/qdgmEESZM/QLhj6vb2LxdfHniBGBMFpuwnnzScNHc5
                    NzYr9XzUJXcfVRsrKaKa1nLl21zeVKqNqf8SwFbJV9a6/UYRnnS84hkawIP6WOLH
                    DHdqN4yQ/I7etFrMgWCeypFLCSN+46GHZw==
                    -----END CERTIFICATE-----',
                'privateKey' => '-----BEGIN PRIVATE KEY-----
                    MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQC+ECfVOVPJSyfo
                    lS9KcBhxP/nyEtErPQnVJtTTFn+9JBmtVguUBvMXr0CVL79t9rkNSchYLta174ld
                    JvNDY/1PH+ZudJVS0mJFR2w+iPdtQVBuEZpP4ts9/xNwQwamxzwmwB+1KSwLjBLU
                    y4GqWkPh6JuNQXySNsHqLZzfjOupZfWYIkIVJp71gShwf1hbv119iNyMrVPY3m1X
                    QtVaCJT9XWC0XH3y/+JsUE87vkw4feBGYUTVr19fS4P/DRsOVXUcTQXT7WEI2tsJ
                    rD/i57HlJaE6ho1NLSS54fcBxe1jWO7hYGWaWgh6dElVCH9aApK14pO/MvCtGS7G
                    3TrXAfNXAgMBAAECggEBAKrvIrPsnAM0eY7+5QpAaGsqC6P/8mi9u6MdCllSKc40
                    sncnJMCbw3NwpVfHGpZOR73AttNARNBZvyOtDSl1uvK3kOmUJlvXZJREGQDg9A4p
                    qKllYXApad6HErdrQIcsNlfvgFTQ05ELCECjSlmoVtbM+WEAHYXug1YWcbjIJ4Yv
                    4jcQwy4hesCW1QfhrvNjNCgURcPjZza8QhrtmCOiuQdJNUq9qWTeZnHnTqsF2IU4
                    K6XNNdLxf2xZ7F+u4hVxcemjti7Nqd5sFkS6jHoKA6KFkdUfepAR5NwE9bZuLHz8
                    ls3g0rVrzMLdhXPI+8zerWzXJbwI6v/xaWxA+XCXwFECgYEA+EOYdd00iZ+owkU/
                    8ST5l104qW272igQl6ATsnRNU0s0N7Opoxdi2WWlc1rDI82HTuO4rlQIf6cfLLpP
                    ssjC1FAihhVgMy3NWxnFByUgZDMVuMsRaIvZ2336Bg4spgjdf9pl5SIhmZS5CuZd
                    U9ntt43PuW3H/zYtg49w9nrQe3kCgYEAw/xKaQADhbW9M+00WaLrs6uSTSvj1jzV
                    zja9gGdjmMMhDq6xSDQBXnvobE5YBA4idBXegNUkaFA38W/EoZtKVPeOtp2d4FTO
                    F8a5lMGoyehz5gJAghdDxr9xxyDC2nhLXxFej5Mblp92v4yj2U/rq99yRwxO6Uco
                    2gamZSU7YU8CgYBY6lzAWelnIPegHI06ILQDsi+I/vQ4vgCzTXHAiEbpfhXFnWM0
                    NjwBAJaxKeCaAhJj/ss2JIKmtYRE0LWaoqykvc6flyhNLCpQZnpahMGFIYa2GISz
                    nOL56bSSVqFHFgW+tMmptv+xscJUVQ036uVoyDGNh/QJQ64pYEZlALeKgQKBgQDB
                    8vJQZssVj3zl3mBoNGq9K5Vk+YJHiXyszk9KuwY9Lx2PwiF/KrgQIN8qD33axYIj
                    D2FabZPSB1DVhZ45r8wnubVp0yFh14r8zJTrOZsn9Pp9LM1Z8FwKW3rlbO5n9ZPh
                    SPcjbplmvfhuJ2gerpCzTjVxSiTthpZO7TXN8sKI0QKBgQCJV1u1K1t+SbosI0L9
                    Ab7UTSjGBfyN5MJk8/S+XFBfR8BEKNF7jLoJRlIGd4gBrCpCRmOfl/0y/LnJkcPn
                    5mpN0HdfQ0UG48FvxovA+mlu88mXn9BGcGkl0wCLOqweLoty8x11bj0F/HzwML+I
                    Oc2LpfBzb+d2XXEU54BlXH5J5A==
                    -----END PRIVATE KEY-----
                    ',
            ],

            'idp' => [
                'entityId' => 'https://sts.windows.net/813dd852-6578-4014-9b75-afb27ac33c28',
                'singleSignOnService' => array (
                    'url' => 'https://login.microsoftonline.com/813dd852-6578-4014-9b75-afb27ac33c28/saml2',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'singleLogoutService' => array (
                    'url' => 'https://login.microsoftonline.com/813dd852-6578-4014-9b75-afb27ac33c28/saml2',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'x509cert' => '-----BEGIN CERTIFICATE-----
                      MIIDBTCCAe2gAwIBAgIQEsuEXXy6BbJCK3bMU6GZ/TANBgkqhkiG9w0BAQsFADAt
                      MSswKQYDVQQDEyJhY2NvdW50cy5hY2Nlc3Njb250cm9sLndpbmRvd3MubmV0MB4X
                      DTE2MTEyNjAwMDAwMFoXDTE4MTEyNzAwMDAwMFowLTErMCkGA1UEAxMiYWNjb3Vu
                      dHMuYWNjZXNzY29udHJvbC53aW5kb3dzLm5ldDCCASIwDQYJKoZIhvcNAQEBBQAD
                      ggEPADCCAQoCggEBAKd6Sq5aJ/zYB8AbWpQWNn+zcnadhcMYezFvPm85NH4VQohT
                      m+FMo3IIJl6JASPSK13m9er3jgPXZuDkdrEDHsF+QMEvqmffS2wHh3tKzasw4U0j
                      RTYB0HSCbmnw9HpUnv/UJ0X/athO2GRmL+KA2eSGmb4+5oOQCQ+qbaRXic/RkAOL
                      Iw1z63kRneLwduQMsFNJ8FZbWkQFj3TtF5SL13P2s/0PnrqwGD59zcbDu9oHOtci
                      u0h++YhF5CWdWEIgafcZk9m+8eY12BKamvPdBnyfpz6GVTenJQe2M+AGz5RSNshv
                      I976VUbBiaIeNzvzaG91m62kFWLRqE3igq6D02ECAwEAAaMhMB8wHQYDVR0OBBYE
                      FAgoZ9HLgFxH2VFGP6PGc4nFizD2MA0GCSqGSIb3DQEBCwUAA4IBAQBSFXalwSJP
                      /jihg04oJUMV2MTbuWtuFhdrdXiIye+UNc/RX02Q9rxd46BfGeKEBflUgNfEHgyE
                      iWTSLAOSDK70vu+ceCVQCGIQPjnGyYOpm80qAj/DNWZujVcSTTV3KZjMFsBVP7mi
                      QowfJQ58u9h8yuJHNhPpB2vOFmNhm4uZq3ve529Xt51HdtQGG9+Z9n1DhObqzkbz
                      8xEFjA+KdfcRsZXa14ZkpAOe35VgyY0f8x34Y0LPfibWcNpfp0AhxKzyqT1GRRlK
                      TjiBA6WNJIJIEeqh/nfOnwM0UQKRnt+2qeV3u00a5lrvJtEy7nq+s7xYtpVAsCvn
                      5T0U1/8IHkxt
                      -----END CERTIFICATE-----',
            ],
            'security' => [
                'lowercaseUrlencoding' => true,
            ],
        ];
        return $sugar_config;
    }
}
