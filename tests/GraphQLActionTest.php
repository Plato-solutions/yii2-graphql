<?php

namespace yiiunit\extensions\graphql;

use yii\filters\auth\QueryParamAuth;
use yii\graphql\filters\auth\CompositeAuth;
use yii\graphql\GraphQLAction;
use yii\web\Controller;

class GraphQLActionTest extends TestCase
{
    /**
     * @var DefaultController
     */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
        $this->controller = new DefaultController('default', \Yii::$app->getModule('graphql'));
    }


    function testAction()
    {
        $_GET = [
            'query' => $this->queries['hello'],
        ];
        $controller = $this->controller;
        $ret = $controller->runAction('index');
        $this->assertNotEmpty($ret);
    }

    function testRunError()
    {
        $_GET = [
            'query' => 'query error{error}',
        ];
        $controller = $this->controller;
        $action = $controller->createAction('index');
        $action->enableSchemaAssertValid = false;
        $ret = $action->runWithParams([]);
        $this->assertNotEmpty($ret);
        $this->assertArrayHasKey('errors', $ret);
    }

    function testAuthBehavior()
    {
        $_GET = [
            'query' => $this->queries['hello'],
            'access-token' => 'testtoken',
        ];
        $controller = $this->controller;
        $controller->attachBehavior('authenticator', [
            'class' => QueryParamAuth::class
        ]);
        $ret = $controller->runAction('index');
        $this->assertNotEmpty($ret);
    }

    function testAuthBehaviorExcept()
    {
        $_GET = [
            'query' => $this->queries['hello'],
        ];
        $controller = $this->controller;
        $controller->attachBehavior('authenticator', [
            'class' => CompositeAuth::class,
            'authMethods' => [
                \yii\filters\auth\QueryParamAuth::class,
            ],
            'except' => ['hello'],
        ]);
        $ret = $controller->runAction('index');
        $this->assertNotEmpty($ret);
    }

    function testIntrospectionQuery()
    {
        $_GET = [
            'query' => $this->queries['introspectionQuery'],
        ];
        $controller = $this->controller;
        $controller->attachBehavior('authenticator', [
            'class' => CompositeAuth::class,
            'authMethods' => [
                \yii\filters\auth\QueryParamAuth::class,
            ],
            'except' => ['__schema'],
        ]);
        $ret = $controller->runAction('index');
        $this->assertNotEmpty($ret);
    }
}
