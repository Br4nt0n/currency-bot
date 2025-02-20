<?php

declare(strict_types=1);

namespace App\Application\Actions\Conversion;

use App\Application\Actions\Action;
use App\Application\Enums\CurrencyEnum;
use App\Application\Services\ConversionInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Throwable;

class ConversionAction extends Action
{
    public function __construct(LoggerInterface $logger, private readonly ConversionInterface $conversion)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $fields = $this->request->getQueryParams();
        $currency = $fields['currency'] ?? null;

        if (!in_array($currency, CurrencyEnum::values())) {
            return $this->respondWithData('Invalid currency', StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
        }

        $amount = (float)$fields['amount'] ?? null;

        if ($amount <= 0) {
            return $this->respondWithData('Invalid amount', StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
        }

        try {
            $result = match ($currency) {
                CurrencyEnum::Peso->value => $this->conversion->pesoConversion($amount),
                CurrencyEnum::Dollar->value => $this->conversion->dollarConversion($amount),
                CurrencyEnum::Ruble->value => $this->conversion->rubleConversion($amount),
            };
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
            return $this->respondWithData('Something went wrong. Try again later.');
        }

        return $this->respondWithData($result);
    }

}
