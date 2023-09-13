<?php

namespace App\Console\Commands;

use App\Services\Chargify\ChargifyFacade;
use App\Services\Chargify\ChargifySystem;
use Illuminate\Console\Command;

class ChargifyTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargify:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected ChargifySystem $chargifySystem)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $userData = [
            'first_name' => 'Martha',
            'last_name' => 'Washington',
            'email' => 'test6@winlocal.com',
            'organization' => 'ABC, Inc.',
            'reference' => '6',
            'address' => '123 Main Street',
            'address_2' => 'Unit 10',
            'city' => 'Anytown',
            'state' => 'MA',
            'zip' => '02120',
            'country' => 'US',
            'phone' => '555-555-1212',
            'locale' => 'es-MX',
        ];

        $data = [
            //            'payment_profile_id' => 0,
            'product_id' => 6542051,
            'product_price_point_id' => 2424017,
            //            'components' => [[
            //                "component_id"=> 2340449,
            //                "allocated_quantity"=> 10,
            //                "price_point_id" => 2978581
            //            ]]
            'metafields' => [
                'user_id' => '6',
                'workspace_id' => '66',
            ],
        ];

        $data['customer_id'] = (ChargifyFacade::customer()->create($userData))['id'];

        $subscription = ChargifyFacade::subscription()->create($data);

        ray($subscription);

        //        try {
        //            try {
        //                $product = ChargifyFacade::product()->listProducts();
        //                echo(json_encode($product));
        //            } catch (\Illuminate\Http\Client\RequestException $exception) {
        //                echo $exception->response->body();
        //            }
        //        } catch (\Throwable $th) {
        //            echo $th->getMessage();
        //        }
        //
        //        return 0;
    }
}
