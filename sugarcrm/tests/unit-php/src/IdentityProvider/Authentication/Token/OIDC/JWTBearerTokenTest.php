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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Token\OIDC;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken
 */
class JWTBearerTokenTest extends TestCase
{
    protected $privateKey = [
        'kty' => 'RSA',
        'kid' => 'private',
        'alg' => 'RS256',
        'n' => 'ziFqqp2RBokiirNkOs1wbJhp4huH_JHABuBBRYFXhfFJY-bKFWHi1SVsDr2rBb_690_H6lEHr04e3lE5L2Ze99hA1eQwjeKHe_' .
            'DtAwKjk7vnG0q08yAupgdsPIrcFtz42kTdxNDCl5sHvNsZIjiY3CUAuutOiVf9ZTmU6-1SYydZa5ApbzmCz7mXgOeuWc6smXX_us5' .
            'uekVHVFiy8c8GDY_GGj_Ber1ejvTOoUiiOL9KY-Wqixpnc-d0fXN-L-4I6MoMVhRV7ynCoJ1FRUTPaSVEKkVJgpRAxZezvJ0641PN' .
            'seL4hhJi1vZlsjeSgm2VQm59nvgLqjVTdN246GHbHWDqk2OKexICYMGsag1PVDPFTvzT9mc5x_ynkbevMBD9GFGgnKYMkEmVFAM9G' .
            'HaE8Ni_WNK1NC2qSLG-AnnIHVPbnfim9FZCgdqORuY406LlkjDS1GGmRmDetJEqQbTaEJ6CywSMo7oQh-monx3ZZHxDTtiyG4B_Xh' .
            'A6jfIb83nljQSZfVSuikwvwLm1TA59OIIJ40PE-olN2gqOayLwhuMhPsi4Tg7huJmDmqPfp9uXMSJh4s2I7XiK5LS8q0ccif1iaFL' .
            '2RxzMLQxT2uv1vRJZoCKoNzR3784rrR75aVXgf-GpfJ1i1utV4nzm7RIyeDaDdb0AJCV648OLAoaKEzU',
        'e' => 'AQAB',
        'd' => 'Dvvm4RgrHqqBVEvOEWg1r-80YzdVH0sJBnbux7qrPhVYHGb-cad38b6SqE-pSvW1rJykD6hsQpYPMGH_Ii7y4Flb_TBlRyscZi' .
            'oRUJK0iVyzZAx-Mt44BeGsQIpnjVHq1RMEe_Yg7xxZ56SVoyMyGW6nKu9H-jvnM6CH7s6FmqeVnHgSSv-HPspi9P_icKzRZyZovI-' .
            'dAE5g7QS1nVZLPlkhMW9JBT8WzJWHH7pD8JQXOEPNrebxdj9w_F2U4q8O_r0RQICh7oy-lSZZjrt9yErpNZlryo40Vyi77A4R5cyF' .
            'u1SgdD6J6M5ofhgEEm8c1oNpplCpqGnP80La2imi39JvWHDgkmmoceQY4DjbQBpUrvGqpnBS_zEskD_F_A7CNQ5ido-cnw_zG9mrJ' .
            'RcRE4lQUcKq-0HLPtORLRliyoxaXw_ToT-fGW9V_uT2TZOPWqpmOJZchZvuwjRCDjsh9bvtA8piJpQwJmj6BG-kx2laAGe7OTrdTx' .
            'IBFAbyMCvEpl1oEL2y9f-8ww6kCw9oimY6IMrRcx6Wr_4BkQYdYvbeG_je-lnYRwyLOZJ9kORJarLk200t-psLIxUSX2YLaZy-QB1' .
            'YXTrxPfd7bgcwdKOtiDXLMX6saqYeVJgRu3lBSfv0DADzIbAvpUHVt3ZNGACwldx9WuZ3_wAqOtBVj9E',
        'p' => '64AdyKYuATRiDS0kcOwOHwTmXAAiPyUCFTuy20ITT8cAN-YTtWH6CyHz1Iu98cI24C9Aq-w8YXoeDG1HP0EHo8xp_1yjr-H8S7' .
            'iNm8Be4YmhKSS-rvqdkWasuOfT0vinm5w0q1anvFRO0IMjWluteM5NWJyWolsYa6F0rfFawr3py3uKVDQMkQaJ5wQo1T8gd7rnYi4' .
            'LYVAym7kAG7wlsz-7I9XI_ERhtpv2HSTEXvqDH9aWnNQ_Lziix6DEVvenxx9Si4DikL3xO7q0LYERO9h_dDcNB7ekHIxI7_hISbwt' .
            '_JZcp_TFLOIJt7JCqjy-kgX-YjL6IB7Ha66nOw57nw',
        'q' => '4BLScxKNbe_7BZkMV6IzgsLrZ1ny8umYU41TV-ZZwmEX2o9-ueYysYs12aDel1gwRwaUOrwuyC9GAfTp8vTs5OL0qWahS2-536' .
            '9X-gQ_x1Pec1HteMpl8B7trJySkvw7V3EHAyDeM_hEpQ2McKv6m1j38xmNI27BDvzwbOMRMdg1RocoNqkmiIAd6-OO8NPQUJE9Y4L' .
            'XFIx0PRj2i3tA7nrVKccMcbG2ECLambEIyadEPXaLfV9b6EmLpCoQL2bj7FQFCP2-pYBSIVe6DkgvzqZRuaa5cW5UldePsEbLxL0v' .
            '3d9TRvDUTmTwHq2cN_9GHvo2YibURGXrU7XvrzKAqw',
    ];

    /**
     * @covers ::getCredentials
     */
    public function testGetCredentials()
    {
        $token = new JWTBearerToken('userId', 'srn:tenant');
        $this->assertNull($token->getCredentials());
    }

    /**
     * @covers ::getIdentity
     */
    public function testGetIdentity()
    {
        $token = new JWTBearerToken('userId', 'srn:tenant');
        $this->assertEquals('userId', $token->getIdentity());
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $expectedResult = 'eyJraWQiOiJLZXlJZCIsImFsZyI6IlJTMjU2In0.eyJpYXQiOjEwLCJleHAiOjMxMCwiYXVkIjoiaHR0' .
            'cDpcL1wvYXVyLnVybCIsInN1YiI6InNybjpjbHVzdGVyOmlkbTpldTowMDAwMDAwMDAxOnVzZXI6c2VlZF9zYWxseV9pZCIsIm' .
            'lzcyI6ImNsaWVudCIsInRpZCI6InNybjpjbHVzdGVyOmlkbTpldTowMDAwMDAwMDAxOnRlbmFudCJ9.UwxUf0cplkrEHGqJyTe' .
            'ZE9d5jiaCwU5CpP0hUDqlhruL_zKrA54tIGAdi4UbkhUuFHKFA8TWJN_ZyU1LPD9A6T5sGYTYA4aaQy8fIysvcnh75aO4pTAuv' .
            '4VXEBmLcPsMvr-Fm524m8K_qXZXPuguZqYAmmazf6MXb0wKS4URtpzAhUYLN0C4U1KR0dYT6PW6X0ryuoq3g072-vvBIAU2y2L1' .
            'Nv_j11jqLgIF31Yjn2Ns7UyQJmrs_OpZ_ZzEoWJ70dcQBwqMQZqYMqzBeZxSy573eDQrxA9Tqq0bCq3OCOPnM2qNeFljA8Cg4D3' .
            'isFJHW3oNR3V8HDO_2JmWevJ13eYzafjxByxZnN69s7D4g-3ykB8pPTZ_WWwpeNtQR1LBwPE1Tx-TYocl3YnG22SCWcDradO0ei' .
            'Dg7nt8z9SRqWE4bVV3nPntKD1b2CXBxeSIcyjnWPh9SShtOAeplTiFcTkDSRutw2KvDkPABEqgER7phwivv0gwdiOCHNCBKQNZt' .
            'FVRIQt8HJ95-aPLWgeb6FFKZl8412y6GJtAW-r6LbNAX14TeGgeNCcFIcT7XWUAlbx0pE3SJVDBpw1Bmq7509NU4pYPzJQ6bVFP' .
            'Pqptj7I12ePJxDTADsMTBGKTDqxKhjqJwCVkPTVQBqNjRKn0I07PtMMQbx3qrtaRizjzLKo';

        $token = $this->getMockBuilder(JWTBearerToken::class)
                      ->setConstructorArgs(
                          ['srn:cluster:idm:eu:0000000001:user:seed_sally_id', 'srn:cluster:idm:eu:0000000001:tenant']
                      )
                      ->setMethods(['getUser'])
                      ->getMock();

        $token->setAttribute('privateKey', $this->privateKey);
        $token->setAttribute('iat', 10);
        $token->setAttribute('aud', 'http://aur.url');
        $token->setAttribute('iss', 'client');
        $token->setAttribute('kid', 'KeyId');

        $this->assertEquals($expectedResult, (string)$token);
        $this->assertEquals('srn:cluster:idm:eu:0000000001:user:seed_sally_id', $token->getIdentity());
    }
}
