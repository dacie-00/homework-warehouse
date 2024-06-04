<?php
declare(strict_types=1);

use App\Ask;
use App\Warehouse\DisplayProducts;
use App\Warehouse\Product;
use App\Warehouse\ProductList;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once "vendor/autoload.php";

function load(string $fileName): ?array
{
    if (file_exists(__DIR__ . "$fileName.json")) {
        return json_decode(
            file_get_contents(__DIR__ . "$fileName.json"),
            false,
            512,
            JSON_THROW_ON_ERROR);
    }
    return null;
}

function save(JsonSerializable $serializable, string $fileName): void
{
    $serializable = json_encode($serializable, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    file_put_contents(__DIR__ . "$fileName.json", $serializable);
}

function validateLogin(string $username, string $password, array $users): bool
{
    foreach ($users as $user) {
        if ($user->username === $username && password_verify($password, $user->password)) {
            return true;
        }
    }
    return false;
}


$input = new ArgvInput();
$output = new ConsoleOutput();

$ask = new Ask($input, $output);
$warehouse = new ProductList(load("/db/products"));
$warehouseDisplay = new DisplayProducts($output);

$logger = new Logger("logger");
$logger->pushHandler(new StreamHandler(__DIR__ . "/db/products.log"));

$users = load("/db/users");
while (true) {
    [$username, $password] = $ask->login();
    if (validateLogin($username, $password, $users)) {
        break;
    }
    echo "Incorrect username or password!\n";
}

echo "Welcome, $username!\n";
$logger->info("$username logged into the database");
while (true) {
    $isWarehouseEmpty = count($warehouse->getAll()) === 0;
    if ($isWarehouseEmpty) {
        echo "The warehouse is empty!\n";
    } else {
        $warehouseDisplay->displayTable($warehouse->getAll());
    }

    $mainAction = $ask->mainAction();
    if ($isWarehouseEmpty && in_array($mainAction, [
            Ask::DELETE_PRODUCT,
            Ask::WITHDRAW_FROM_PRODUCT,
            Ask::ADD_TO_PRODUCT,
        ], true)) {
        echo "You cannot do this as there are no products in the warehouse!\n";
        continue;
    }
    switch ($mainAction) {
        case Ask::ADD_NEW_PRODUCT:
            [$name, $quantity] = $ask->productInfo();
            $warehouse->add(new Product($name, $quantity));

            $logger->info("$username added the product $name to warehouse");
            save($warehouse, "/db/products");
            break;
        case Ask::DELETE_PRODUCT:
            $product = $warehouse->get($ask->product($warehouse->getAll()));
            $warehouse->delete($product);

            $logger->info("$username deleted the product {$product->getName()} from warehouse");
            save($warehouse, "/db/products");
            break;
        case ASK::ADD_TO_PRODUCT:
            $product = $warehouse->get($ask->product($warehouse->getAll()));
            $quantity = $ask->quantity(1);
            $product->setQuantity($product->getQuantity() + $quantity);
            $product->updateUpdatedAt();

            $logger->info("$username added $quantity to the {$product->getName()} stock");
            save($warehouse, "/db/products");
            break;
        case ASK::WITHDRAW_FROM_PRODUCT:
            $product = $warehouse->get($ask->product($warehouse->getAll()));
            if ($product->getQuantity() === 0) {
                echo "You cannot withdraw any of this product, as there is 0 of it in stock!\n";
                continue 2;
            }
            $quantity = $ask->quantity(1, $product->getQuantity());
            $product->setQuantity($product->getQuantity() - $quantity);
            $product->updateUpdatedAt();

            $logger->info("$username removed $quantity from the {$product->getName()} stock");
            save($warehouse, "/db/products");
            break;
        case Ask::EXIT:
            break 2;
    }
}
