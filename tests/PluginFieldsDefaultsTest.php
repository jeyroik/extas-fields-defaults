<?php
namespace tests;

use Dotenv\Dotenv;
use extas\components\conditions\Condition;
use extas\components\conditions\ConditionLike;
use extas\components\conditions\ConditionRepository;
use extas\components\extensions\TSnuffExtensions;
use extas\components\fields\Field;
use extas\components\fields\FieldRepository;
use extas\components\Item;
use extas\components\parsers\Parser;
use extas\components\parsers\ParserOneOf;
use extas\components\parsers\ParserRepository;
use extas\components\plugins\Plugin;
use extas\components\plugins\PluginFieldsDefaults;
use extas\components\plugins\PluginRepository;
use extas\interfaces\conditions\ICondition;
use extas\interfaces\conditions\IConditionRepository;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\samples\parameters\ISampleParameter;
use PHPUnit\Framework\TestCase;

/**
 * Class PluginFieldsDefaultsTest
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class PluginFieldsDefaultsTest extends TestCase
{
    use TSnuffExtensions;

    protected IRepository $fieldRepo;
    protected IRepository $pluginRepo;
    protected IRepository $parserRepo;
    protected IRepository $condRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->fieldRepo = new FieldRepository();
        $this->pluginRepo = new PluginRepository();
        $this->parserRepo = new ParserRepository();
        $this->condRepo = new ConditionRepository();
        $this->addReposForExt([
            'fieldRepository' => FieldRepository::class,
            'parserRepository' => ParserRepository::class,
            'conditionRepository' => ConditionRepository::class,
            IConditionRepository::class => ConditionRepository::class
        ]);
    }

    protected function tearDown(): void
    {
        $this->fieldRepo->delete([Field::FIELD__NAME => 'test']);
        $this->pluginRepo->delete([Plugin::FIELD__CLASS => PluginFieldsDefaults::class]);
        $this->parserRepo->delete([Parser::FIELD__NAME => 'test']);
        $this->condRepo->delete([ICondition::FIELD__NAME => 'like']);
        $this->deleteSnuffExtensions();
    }

    public function testDefaults()
    {
        $this->fieldRepo->create(new Field([
            Field::FIELD__NAME => 'test',
            Field::FIELD__VALUE => 'is ok',
            Field::FIELD__PARAMETERS => [
                'subject' => [
                    ISampleParameter::FIELD__NAME => 'subject',
                    ISampleParameter::FIELD__VALUE => 'test'
                ]
            ]
        ]));
        $this->pluginRepo->create(new Plugin([
            Plugin::FIELD__CLASS => PluginFieldsDefaults::class,
            Plugin::FIELD__STAGE => 'test.init'
        ]));
        $this->createRepoExt(['fieldRepository', 'parserRepository']);

        $test = new class extends Item {
            protected function getSubjectForExtension(): string
            {
                return 'test';
            }
        };

        $this->assertEquals('is ok', $test['test']);
    }

    public function testCallableValue()
    {
        $this->fieldRepo->create(new Field([
            Field::FIELD__NAME => 'test',
            Field::FIELD__VALUE => ExternalValue::class,
            Field::FIELD__PARAMETERS => [
                'subject' => [
                    ISampleParameter::FIELD__NAME => 'subject',
                    ISampleParameter::FIELD__VALUE => 'test'
                ]
            ]
        ]));
        $this->pluginRepo->create(new Plugin([
            Plugin::FIELD__CLASS => PluginFieldsDefaults::class,
            Plugin::FIELD__STAGE => 'test.init'
        ]));

        $test = new class extends Item {
            protected function getSubjectForExtension(): string
            {
                return 'test';
            }
        };

        $this->assertEquals('is ok', $test['test']);
    }

    public function testParserUsing()
    {
        $this->fieldRepo->create(new Field([
            Field::FIELD__NAME => 'test',
            Field::FIELD__VALUE => '@oneof(is ok,is well)',
            Field::FIELD__PARAMETERS => [
                'subject' => [
                    ISampleParameter::FIELD__NAME => 'subject',
                    ISampleParameter::FIELD__VALUE => 'test'
                ]
            ]
        ]));
        $this->pluginRepo->create(new Plugin([
            Plugin::FIELD__CLASS => PluginFieldsDefaults::class,
            Plugin::FIELD__STAGE => 'test.init'
        ]));
        $this->parserRepo->create(new Parser([
            Parser::FIELD__NAME => 'test',
            Parser::FIELD__CLASS => ParserOneOf::class,
            Parser::FIELD__VALUE => 'oneof',
            Parser::FIELD__CONDITION => '~',
            Parser::FIELD__PARAMETERS => [
                'pattern' => [
                    ISampleParameter::FIELD__NAME => 'pattern',
                    ISampleParameter::FIELD__VALUE => '/\@oneof\((.*)\)/'
                ],
                'delimiter' => [
                    ISampleParameter::FIELD__NAME => 'delimiter',
                    ISampleParameter::FIELD__VALUE => ','
                ]
            ]
        ]));
        $this->condRepo->create(new Condition([
            Condition::FIELD__NAME => 'like',
            Condition::FIELD__ALIASES => ['like', '~'],
            Condition::FIELD__CLASS => ConditionLike::class
        ]));

        $test = new class extends Item {
            protected function getSubjectForExtension(): string
            {
                return 'test';
            }
        };

        $this->assertTrue(in_array($test['test'], ['is ok', 'is well']));
    }

    public function testClassNameIsReturning()
    {
        $this->fieldRepo->create(new Field([
            Field::FIELD__NAME => 'test',
            Field::FIELD__VALUE => 'extas\\components\\plugins\\Plugin',
            Field::FIELD__PARAMETERS => [
                'subject' => [
                    ISampleParameter::FIELD__NAME => 'subject',
                    ISampleParameter::FIELD__VALUE => 'test'
                ]
            ]
        ]));
        $this->pluginRepo->create(new Plugin([
            Plugin::FIELD__CLASS => PluginFieldsDefaults::class,
            Plugin::FIELD__STAGE => 'test.init'
        ]));

        $test = new class extends Item {
            protected function getSubjectForExtension(): string
            {
                return 'test';
            }
        };

        $this->assertEquals(Plugin::class, $test['test']);
    }
}
