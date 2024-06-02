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

    private function load(string $fileName): ?stdClass
    {
        if (file_exists(__DIR__ . "/db/$fileName.json")) {
            return json_decode(file_get_contents(__DIR__ . "/db/$fileName.json"));
        }
        return null;
    }

    private function save(JsonSerializable $serializable, string $fileName): void
    {
        $serializable = json_encode($serializable, JSON_PRETTY_PRINT);
        if (!is_dir(__DIR__ . "/db")) {
            mkdir(__DIR__ . "/db");
        }
        file_put_contents(__DIR__ . "/db/$fileName.json", $serializable);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ask = new Ask($input, $output);
        $warehouse = new ProductCollection($this->load("products"));

        while (true) {
            foreach ($warehouse->getAll() as $item) {
                echo "{$item->getName()} - {$item->getQuantity()}\n";
            }
            $mainAction = $ask->mainAction();
            switch ($mainAction) {
                case Ask::ADD_NEW_PRODUCT:
                    [$name, $quantity] = $ask->productInfo();
                    $warehouse->add(new Product(Uuid::uuid4()->toString(), $name, $quantity));
                    $this->save($warehouse, "products");
                    break;
                case Ask::DELETE_PRODUCT:
                    $id = $ask->product($warehouse->getAll());
                    $warehouse->delete($warehouse->get($id));
                    $this->save($warehouse, "products");
                    break;
                case ASK::ADD_PRODUCT:
                    $id = $ask->product($warehouse->getAll());
                    $product = $warehouse->get($id);
                    $quantity = $ask->quantity(0);
                    $product->setQuantity($product->getQuantity() + $quantity);
                    $this->save($warehouse, "products");
                    break;
                case ASK::WITHDRAW_PRODUCT:
                    $id = $ask->product($warehouse->getAll());
                    $product = $warehouse->get($id);
                    $quantity = $ask->quantity(1, $product->getQuantity());
                    $product->setQuantity($product->getQuantity() + $quantity);
                    $this->save($warehouse, "products");
                    break;
                case Ask::EXIT:
                    return Command::SUCCESS;
            }
        }
    }
};

$application->add($start);
$application->setDefaultCommand("start");
$application->run();