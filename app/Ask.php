<?php
declare(strict_types=1);

namespace App;

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

    private const ADD_PRODUCT = "add product";
    private const DELETE_PRODUCT = "delete product";

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = new QuestionHelper();
    }

    public function mainAction(): string
    {
        $question = new ChoiceQuestion("What do you want to do?", [$this::ADD_PRODUCT, $this::DELETE_PRODUCT]);
        return $this->helper->ask($this->input, $this->output, $question);
    }

    /**
     * @return array{product: string, quantity:int}
     */
    public function productInfo(): array
    {
        $nameQuestion = new Question("What is the product?");
        $name = $this->helper->ask($this->input, $this->output, $nameQuestion);
        $quantityQuestion = (new Question("How much of it do you want to put in to the warehouse?"))
            ->setValidator(function ($input) { // TODO: check if there is a cleaner way to do this
                return $this->quantityValidator($input);
            });
        $quantity = (int)$this->helper->ask($this->input, $this->output, $quantityQuestion);
        return [$name, $quantity];
    }

    private function quantityValidator($input)
    {
        if (!is_numeric($input)) {
            throw new \Exception("Value must be a number");
        }
        if ($input <= 0) {
            throw new \Exception("Value must be greater than 0");
        }
        return $input;
    }
}