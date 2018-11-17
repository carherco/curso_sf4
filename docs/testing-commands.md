Testeo de comandos de consola
=============================

Symfony proporciona una clase **CommandTester** para facilitar el testeo de comandos. Esta clase permite lanzar comandos desde un test pasando argumentos y opciones y devolviéndonos la salida que se generaría.

```php
// tests/Command/CreateUserCommandTest.php
namespace App\Tests\Command;

use App\Command\CreateUserCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new CreateUserCommand());

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'username' => 'Wouter', // arguments
            '--some-option' => 'option_value', // options (se pasan con --),
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Username: Wouter', $output);

        // ...
    }
}
```
