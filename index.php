<?php
declare(strict_types=1);

use App\Ask;
use App\Product;
use App\ProductCollection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once "vendor/autoload.php";

$application = new Application();

$start = new class extends Command {
    protected static $defaultName = "start";

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ask = new Ask($input, $output);
        $warehouse = new ProductCollection();
        while (true) {
            $mainAction = $ask->mainAction();
            switch ($mainAction) {
                case Ask::ADD_PRODUCT:
                    [$name, $quantity] = $ask->productInfo();
                    $warehouse->add(new Product(Uuid::uuid4()->toString(), $name, $quantity));
                    break;
                case Ask::DELETE_PRODUCT:
                    $id = $ask->product($warehouse->getAll());
                    $warehouse->delete($warehouse->get($id));
                    break;
                case Ask::EXIT:
                    exit("Bye!");
            }
            foreach ($warehouse->getAll() as $item) {
                echo "{$item->getName()} - {$item->getQuantity()}\n";
            }
        }

        return Command::SUCCESS;
    }
};

$application->add($start);
$application->setDefaultCommand("start");
$application->run();