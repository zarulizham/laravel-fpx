<?php

namespace ZarulIzham\Fpx;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use ZarulIzham\Fpx\Exceptions\InvalidCertificateException;
use ZarulIzham\Fpx\Messages\AuthEnquiry;
use ZarulIzham\Fpx\Messages\BankEnquiry;
use ZarulIzham\Fpx\Models\Bank;

class Fpx
{
    /**
     * Route authorization callback.
     */
    protected static ?Closure $authUsing = null;

    /**
     * Register route authorization callback.
     */
    public static function auth(Closure $callback): void
    {
        static::$authUsing = $callback;
    }

    /**
     * Returns whether request is authorized to access FPX transaction routes.
     */
    public static function check(Request $request): bool
    {
        if (static::$authUsing instanceof Closure) {
            return (bool) call_user_func(static::$authUsing, $request);
        }

        return (bool) $request->user();
    }

    /**
     * returns collection of bank_id and name
     *
     * @param  bool  $getLatest  (optional) pass true to get latest banks
     * @return \Illuminate\Support\Collection
     */
    public static function getBankList(bool $getLatest = false)
    {
        if ($getLatest) {
            try {
                $bankEnquiry = new BankEnquiry;
                $dataList = $bankEnquiry->getData();
                $response = $bankEnquiry->connect($dataList);
                $token = strtok($response, '&');
                $bankList = $bankEnquiry->parseBanksList($token);

                if ($bankList === false) {
                    throw new Exception('We could not find any data');
                }

                foreach ($bankList as $key => $status) {
                    $bankId = explode(' - ', $key)[1];
                    $bank = $bankEnquiry->getBanks($bankId, 'B2C');

                    if (empty($bank)) {
                        logger('Bank Not Found: ', [$bankId]);

                        continue;
                    }

                    Bank::updateOrCreate([
                        'bank_id' => $bankId,
                        'type' => $bank['type'],
                    ], [
                        'status' => $status == 'A' ? 'Online' : 'Offline',
                        'name' => $bank['name'],
                        'short_name' => $bank['short_name'],
                        'position' => $bank['position'],
                    ]);
                }
            } catch (Exception $e) {
                \Log::warning($e->getMessage());
            }
        }

        return Bank::select('name', 'bank_id', 'short_name', 'status')
            ->orderBy('position', 'ASC')
            ->get();
    }

    /**
     * Returns status of transaction
     *
     * @param  string  $order_number  order number
     * @return array
     */
    public static function getTransactionStatus(string $order_number, ?string $exchange_order_number = null)
    {
        try {
            $authEnquiry = new AuthEnquiry;
            $authEnquiry->handle([
                'order_number' => $order_number,
                'exchange_order_number' => $exchange_order_number,
            ]);

            $dataList = $authEnquiry->getData();
            $response = $authEnquiry->connect($dataList);

            $token = strtok($response, '&');

            $responseData = $authEnquiry->parseResponse($token);

            if ($responseData === false) {
                return [
                    'status' => 'failed',
                    'message' => 'We could not find any data',
                    'transaction_id' => null,
                    'order_number' => $order_number,
                    'exchange_order_number' => $exchange_order_number,
                    'amount' => null,
                    'transaction_timestamp' => null,
                    'buyer_bank_name' => null,
                    'response_format' => null,
                    'additional_params' => null,
                ];
            }

            return $responseData;
        } catch (ModelNotFoundException $e) {
            return [
                'status' => 'failed',
                'message' => 'Invalid reference Id',
                'transaction_id' => null,
                'order_number' => $order_number,
                'exchange_order_number' => $exchange_order_number,
                'amount' => null,
                'transaction_timestamp' => null,
                'buyer_bank_name' => null,
                'response_format' => null,
                'additional_params' => null,
            ];
        } catch (InvalidCertificateException $e) {
            return [
                'status' => 'failed',
                'message' => 'Failed to verify the request origin',
                'transaction_id' => null,
                'order_number' => $order_number,
                'exchange_order_number' => $exchange_order_number,
                'amount' => null,
                'transaction_timestamp' => null,
                'buyer_bank_name' => null,
                'response_format' => null,
                'additional_params' => null,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'transaction_id' => null,
                'order_number' => $order_number,
                'exchange_order_number' => $exchange_order_number,
                'amount' => null,
                'transaction_timestamp' => null,
                'buyer_bank_name' => null,
                'response_format' => null,
                'additional_params' => null,
            ];
        }
    }
}
