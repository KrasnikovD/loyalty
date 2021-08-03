<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Models\Product;
use Illuminate\Console\Command;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import_products {f}';

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
    public function __construct()
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
        $file = $this->argument('f');
        $categoriesMap = [];
        foreach (Categories::select('categories.id', 'categories.name')->get() as $item) {
            $categoriesMap[$item->name] = $item->id;
        }
        $productsList = [];
        foreach (Product::all() as $item) {
            $productsList[$item->code] = $item;
        }
        $registeredCodes = array_keys($productsList);

        foreach (json_decode(file_get_contents($file)) as $item) {
            if (intval($item->price) == 0) continue;
            if (in_array($item->code, $registeredCodes)) {
                print "edit product: {$item->code}\n";
                $product = $productsList[$item->code];
                if (Categories::where('id', $product->category_id)->first()->name == Categories::DEFAULT_NAME) {
                    if (!in_array($item->category, array_keys($categoriesMap))) {
                        print "new category: {$item->category}\n";
                        $category = new Categories;
                        $category->is_imported = 1;
                        $category->parent_id = 0;
                        $category->name = $item->category;
                        $category->save();
                        $categoriesMap[$item->category] = $category->id;
                    } else {
                        print "category exist: {$item->category}\n";
                    }
                    $product->category_id = $categoriesMap[$item->category];
                }
                $product->price = intval(str_replace(' ', '', $item->price));
                $product->name = $item->name;
                $product->description = $item->name;
                $product->is_by_weight = $item->unit == 'кг';
                $product->archived = 0;
                $product->save();
            } else {
                print "new product: {$item->code}\n";
                if (!in_array($item->category, array_keys($categoriesMap))) {
                    print "new category: {$item->category}\n";
                    $category = new Categories;
                    $category->is_imported = 1;
                    $category->parent_id = 0;
                    $category->name = $item->category;
                    $category->save();
                    $categoriesMap[$item->category] = $category->id;
                } else {
                    print "category exist: {$item->category}\n";
                }
                $product = new Product;
                $product->is_imported = 1;
                $product->category_id = $categoriesMap[$item->category];
                $product->name = $item->name;
                $product->description = $item->name;
                $product->code = $item->code;
                $product->price = intval(str_replace(' ', '', $item->price));
                $product->is_by_weight = $item->unit == 'кг';
                $product->save();
            }
            unset($productsList[$item->code]);
        }

        foreach ($productsList as $item) {
            if ($item->is_imported) {
                print "archived: {$item->code}\n";
                $item->archived = 1;
                $item->save();
            }
        }

        return 0;
    }
}
