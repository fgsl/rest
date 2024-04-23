<?php
/**
 * Fgsl REST
 *
 * @author    Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @link      https://github.com/fgsl/rest for the canonical source repository
 * @copyright Copyright (c) 2023 FGSL (http://www.fgsl.eti.br)
 * @license   https://www.gnu.org/licenses/agpl.txt GNU AFFERO GENERAL PUBLIC LICENSE
 */
namespace Fgsl\Test;

use Fgsl\Rest\Rest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * 
 * @package    Fgsl
 * @subpackage Test
 * @covers Rest
 */
#[CoversClass('Rest')]
class RestTest extends TestCase
{
    public function testGet()
    {
        $rest = new Rest();
        $this->assertTrue(is_object($rest));
        
        @$response = $rest->doGet([],'http://www.horalegalbrasil.mct.on.br/SincronismoPublico.html',200);
        
        $this->assertStringContainsString('Sincronismo', $response);

        @$response = $rest->doGet([],'http://www.horalegalbrasil.mct.on.br/SincronismoPublico.html',[200,201]);
        
        $this->assertStringContainsString('Sincronismo', $response);

        @$response = $rest->doGet([],'http://www.horalegalbrasil.mct.on.br/SincronismoPublico.html',500);
        
        $this->assertEquals(1,count($rest->requestErrors));

        $this->assertEquals(3,$rest->requestCounter);
    }

    public function testPost()
    {
        $rest = new Rest();

        $data = [
            'name' => 'morpheus',
            'job' => 'leader'
        ];
        
        @$response = $rest->doPost($data, [],'https://reqres.in/api/users',201);
        
        $this->assertStringContainsString('createdAt', $response);
    }

    public function testPut()
    {
        $rest = new Rest();

        $data = [
            'name' => 'morpheus',
            'job' => 'general'
        ];
        
        @$response = $rest->doPut($data, [],'https://reqres.in/api/users/2',201);
        
        $this->assertStringContainsString('updatedAt', $response);

        @$response = $rest->doPut($data, [],'https://reqres.in/api/users/2',[200,201]);
        
        $this->assertStringContainsString('updatedAt', $response);
    }

    public function testPatch()
    {
        $rest = new Rest();

        $data = [
            'name' => 'morpheus',
            'job' => 'zion resident'
        ];
        
        @$response = $rest->doPatch($data, [], 'https://reqres.in/api/users/2', 200);
        
        $this->assertStringContainsString('updatedAt', $response);

        @$response = $rest->doPatch($data, [], 'https://reqres.in/api/users/2', [200,201]);
        
        $this->assertStringContainsString('updatedAt', $response);
    }

    public function testDelete()
    {
        $rest = new Rest();
        
        @$response = $rest->doDelete([],'https://reqres.in/api/users/2',204);

        $this->assertEquals(0,count($rest->requestErrors));

        @$response = $rest->doDelete([],'https://reqres.in/api/users/2',[200,204]);

        $this->assertEquals(0,count($rest->requestErrors));
    }
}
