<?php
declare(strict_types=1);

namespace App;

use App\Warehouse\Product;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Ask
{
    private InputInterface $input;
    private OutputInterface $output;
    private QuestionHelper $helper;

    public const ADD_NEW_PRODUCT = "add new product";
    public const DELETE_PRODUCT = "delete product";
    public const ADD_TO_PRODUCT = "add to product";
    public const WITHDRAW_FROM_PRODUCT = "withdraw from product";
    public const EXIT = "exit";

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = new QuestionHelper();
    }

    public function mainAction(): string
    {
        $question = new ChoiceQuestion("What do you want to do?", [
            $this::ADD_NEW_PRODUCT,
            $this::DELETE_PRODUCT,
            $this::ADD_TO_PRODUCT,
            $this::WITHDRAW_FROM_PRODUCT,
            $this::EXIT,
        ]);
        return $this->helper->ask($this->input, $this->output, $question);
    }

    /**
     * @param Product[] $products
     */
    public function product(array $products): string
    {
        $names = [];
        foreach ($products as $product) {
            $names[] = "{$product->getName()} ({$product->getId()})";
        }
        $question = new ChoiceQuestion("Select a product", $names);
        $answer = $this->helper->ask($this->input, $this->output, $question);
        $id = substr($answer, strrpos($answer, "("));
        return trim($id, "()");
    }

    /**
     * @return array{product: string, quantity:int}
     */
    public function productInfo(): array
    {
        $nameQuestion = new Question("What is the product name? ");
        $name = $this->helper->ask($this->input, $this->output, $nameQuestion);
        $quantity = $this->quantity();
        return [$name, $quantity]; // TODO: do this in a cleaner way
    }

    private function quantityValidator(string $input, $min, $max): string
    {
        if (!is_numeric($input)) {
            throw new \Exception("Quantity must be a number");
        }
        if ($input < $min) {
            throw new \Exception("Quantity must be greater than or equal to $min");
        }
        if ($input > $max) {
            throw new \Exception("Quantity must be less than or equal to $max");
        }
        return $input;
    }

    public function quantity(int $min = 0, $max = 9999999): int
    {
        $quantityQuestion = (new Question("Enter the quantity ($min-$max) "))
            ->setValidator(function ($input) use ($min, $max): string { // TODO: check if there is a cleaner way to do this
                return $this->quantityValidator($input, $min, $max);
            });
        return (int)$this->helper->ask($this->input, $this->output, $quantityQuestion);
    }


    /**
     * @return array{username: string, password: string} // TODO: maybe remove the keys here as they are actually not returned
     */
    public function login(): array
    {
        $usernameQuestion = new Question("Enter your username ");
        $username = $this->helper->ask($this->input, $this->output, $usernameQuestion);
        $passwordQuestion = new Question("Enter your password ");
        $passwordQuestion->setHidden(true);
        $password = $this->helper->ask($this->input, $this->output, $passwordQuestion);
        return [$username, $password];
    }
}