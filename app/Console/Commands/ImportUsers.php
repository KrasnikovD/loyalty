<?php

namespace App\Console\Commands;

use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\Cards;
use App\Models\CommonActions;
use App\Models\Fields;
use App\Models\FieldsUsers;
use App\Models\Users;
use Illuminate\Console\Command;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import_users_csv {g} {a}';

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
        $pathToGuest = $this->argument('g');
        $pathToActiveCard = $this->argument('a');
        if (!file_exists($pathToGuest) || !file_exists($pathToActiveCard))
            die("Invalid arguments");

        $activeCardList = file($pathToActiveCard);
        array_walk($activeCardList, function (&$value, $key) {
            $value = str_replace(';', '', $value);
            $value = trim($value);
        });

        $data = [];
        foreach (str_getcsv(file_get_contents($pathToGuest), "\n") as &$row) {
            $row = str_getcsv($row, ";");
            $phone = $row[1];
            if ($phone == 'телефон') continue;
            if (strpos($phone, 'E+') !== false) continue;

            $cardNumber = trim(str_replace(' ', '', $row[2]));
            if (strlen($cardNumber) > 8) continue;

            $cardNumber = str_repeat('0', 8 - strlen($cardNumber)) . $cardNumber;
            if (!in_array($cardNumber, $activeCardList)) continue;

            if (empty($phone) || $phone == '80') $phone = "empty_".uniqid().'_'.uniqid();
            else $phone = substr_replace($phone, '+7', 0, 1);
            if (!isset($data[$phone])) $data[$phone] = [];
            $row[2] = $cardNumber;
            $row[1] = $phone;
            $data[$phone][] = $row;
        }

        $billPrograms = BillPrograms::orderBy('to', 'desc')->get();

        array_walk($data, function ($userArray, $key) use ($billPrograms) {
            @list($secondName, $firstName, $thirdName) = explode(' ', $userArray[0][0]);
            $firstName = self::mbUcfirst($firstName);
            if ($secondName) $secondName = self::mbUcfirst($secondName);
            if ($thirdName) $thirdName = self::mbUcfirst($thirdName);
            $phone = $userArray[0][1];
            $birthday = date('Y-m-d', strtotime($userArray[0][5]));
            $userId = self::createUser($firstName, $secondName, $thirdName, $phone, $birthday);
            foreach ($userArray as $item) {
                $cardNumber = $item[2];
                if (Cards::where('number', $cardNumber)->exists()) {
                    print "Card $cardNumber already exists\n";
                    continue;
                }
                $bonusValue = abs(floatval($item[3]));
                $currentAmount = floatval(str_replace(' ', '', $item[4]));
                print $phone.' '.$cardNumber.' '.$bonusValue.' '.$currentAmount."\n";
                $holderName = $item[0];
                self::createCard($userId, $cardNumber, $bonusValue, $currentAmount, $phone, $holderName, $billPrograms);
            }
            print "**************\n";
        });
        return 0;
    }

    private static function createCard($userId, $cardNumber, $bonusValue, $currentAmount, $phone, $holderName, $billPrograms)
    {
        $remainingAmount = null;
        if ($billPrograms) {
            $program = null;
            $maxProgram = $billPrograms[0];
            if ($currentAmount >= $maxProgram->to)
                $program = $maxProgram;
            foreach ($billPrograms as $row) {
                if ($currentAmount >= $row->from && $currentAmount <= $row->to) {
                    $program = $row;
                    break;
                }
            }
            $currentFrom = 0;
            $currentTo = 0;
            if ($program) {
                $currentFrom = $program->from;
                $currentTo = $program->to;
            }
            $nextFrom = BillPrograms::where('from', '>', $currentFrom)->min('from');
            if (!$nextFrom) $nextFrom = $currentTo + 1;
            $remainingAmount = ($currentAmount > $maxProgram->to) ? 0 : $nextFrom - $currentAmount;
            print $currentAmount.' '.$program->percent.' '.$remainingAmount."\n";
        }
        $card = new Cards;
        $card->number = $cardNumber;
        $card->phone = $phone;
        $card->old_holder_name = $holderName;
        $card->user_id = $userId;
        $card->is_physical = 1;
        $card->is_main = 1;
        $card->save();

        $bill = new Bills;
        $bill->card_id = $card->id;
        $bill->bill_type_id = BillTypes::where('name', BillTypes::TYPE_DEFAULT)->first()->id;
        $bill->bill_program_id = @$program->id;
        $bill->remaining_amount = $remainingAmount;
        $bill->value = $bonusValue;
        $bill->save();

        CommonActions::cardHistoryLogEditOrCreate($card, true);
    }

    private static function createUser($firstName, $secondName, $thirdName, $phone, $birthday)
    {
        $isNew = true;
        $user = Users::where('phone', $phone)->first();
        if (!$user) {
            print "New user: $phone\n";
            $user = new Users;
        } else {
            $isNew = false;
            print "Edit user: {$user->id}\n";
        }
        $user->first_name = $firstName;
        $user->second_name = $secondName;
        $user->third_name = $thirdName;
        $user->birthday = $birthday;
        if ($isNew) {
            $user->phone = $phone;
            $user->type = Users::TYPE_USER;
            $user->token = sha1(microtime() . 'salt' . time());
        }
        $user->save();

        if ($isNew) {
            foreach (Fields::all() as $field) {
                $fieldsUser = new FieldsUsers;
                $fieldsUser->field_id = $field->id;
                $fieldsUser->user_id = $user->id;
                $fieldsUser->save();
            }
        }

        return $user->id;
    }

    private static function mbUcfirst($string)
    {
        $string = mb_strtolower($string, 'UTF-8');
        $firstChar = mb_substr($string, 0, 1, 'UTF-8');
        return mb_strtoupper($firstChar, 'UTF-8') . mb_substr($string, 1, null, 'UTF-8');
    }
}
