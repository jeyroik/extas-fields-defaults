<?php
namespace tests;

use Dotenv\Dotenv;
use extas\components\extensions\TSnuffExtensions;
use extas\components\fields\Field;
use extas\components\fields\FieldRepository;
use extas\components\Item;
use extas\components\plugins\Plugin;
use extas\components\plugins\PluginFieldsDefaults;
use extas\components\plugins\PluginRepository;
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

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->fieldRepo = new FieldRepository();
        $this->pluginRepo = new PluginRepository();
        $this->addReposForExt([
            'fieldRepository' => FieldRepository::class
        ]);
    }

    protected function tearDown(): void
    {
        $this->deleteSnuffExtensions();
        $this->fieldRepo->delete([Field::FIELD__NAME => 'test']);
        $this->pluginRepo->delete([Plugin::FIELD__CLASS => PluginFieldsDefaults::class]);
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
        $this->createRepoExt(['fieldRepository']);

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
        $this->createRepoExt(['fieldRepository']);

        $test = new class extends Item {
            protected function getSubjectForExtension(): string
            {
                return 'test';
            }
        };

        $this->assertEquals('is ok', $test['test']);
    }
}
