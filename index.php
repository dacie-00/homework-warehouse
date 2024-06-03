<?php
declare(strict_types=1);

use App\Ask;
use App\Product;
use App\ProductCollection;
use App\ProductDisplay;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once "vendor/autoload.php";

$application = new Application();

$start = new class extends Command {
    protected static $defaultName = "start";

    private function load(string $fileName)
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

    private function loginValidate(string $username, string $password, array $users): bool
    {
        foreach ($users as $user) {
            if ($user->username === $username && password_verify($password, $user->password)) {
                return true;
            }
        }
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO: fix edge case of trying to withdraw a product with 0 stock
        $ask = new Ask($input, $output);
        $warehouse = new ProductCollection($this->load("products"));
        $warehouseDisplay = new ProductDisplay($output);

        $logger = new Logger("logger");
        $logger->pushHandler(new StreamHandler(__DIR__ . "/db/products.log"));

        $users = $this->load("users");
        while (true) {
            [$username, $password] = $ask->login();
            if ($this->loginValidate($username, $password, $users)) {
                break;
            }
            echo "Incorrect username or password!\n";
        }

        echo "Welcome, $username!\n";
        while (true) {
            $isWarehouseEmpty = count($warehouse->getAll()) == 0;
            if ($isWarehouseEmpty) {
                echo "The warehouse is empty!\n";
            } else {
                $warehouseDisplay->displayTable($warehouse->getAll());
            }
            $mainAction = $ask->mainAction();
            if ($isWarehouseEmpty && in_array($mainAction, [
                    Ask::DELETE_PRODUCT,
                    Ask::WITHDRAW_PRODUCT,
                    Ask::ADD_PRODUCT
                ])) {
                echo "You cannot do this as there are no products in the warehouse!\n";
                continue;
            }
            switch ($mainAction) {
                case Ask::ADD_NEW_PRODUCT:
                    [$name, $quantity] = $ask->productInfo();
                    $warehouse->add(new Product($name, $quantity));
                    $logger->info("$username added the product $name to warehouse");
                    $this->save($warehouse, "products");
                    break;
                case Ask::DELETE_PRODUCT:
                    $id = $ask->product($warehouse->getAll());
                    $product = $warehouse->get($id);
                    $warehouse->delete($product);
                    $logger->info("$username deleted the product {$product->getName()} from warehouse");
                    $this->save($warehouse, "products");
                    break;
                case ASK::ADD_PRODUCT:
                    $id = $ask->product($warehouse->getAll());
                    $product = $warehouse->get($id);
                    $quantity = $ask->quantity();
                    $product->setQuantity($product->getQuantity() + $quantity);
                    $logger->info("$username added $quantity to the {$product->getName()} stock");
                    $this->save($warehouse, "products");
                    break;
                case ASK::WITHDRAW_PRODUCT:
                    $id = $ask->product($warehouse->getAll());
                    $product = $warehouse->get($id);
                    $quantity = $ask->quantity(1, $product->getQuantity());
                    $product->setQuantity($product->getQuantity() - $quantity);
                    $logger->info("$username removed $quantity from the {$product->getName()} stock");
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