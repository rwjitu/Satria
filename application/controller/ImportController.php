<?php


class ImportController extends Controller
{

    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise not-logged in users could do actions. If all of your pages should only
        // be usable by logged-in users: Put this line into libs/Controller->__construct
        Auth::checkAuthentication();
        //echo 'exit;'; exit;
        
    }

    public function index()
    {
        echo "import only please :)";
    }

    /**
     * import nasabah baru dengan existing account number and saldo pertama
     * format: nomer_rekening - nama_nasabah - saldo pertama
     */
    public function importMaterial()
    {

        $seed = '
PG-BBN.012.030.3 -_- BAUT GRON M 12 x 30 mm -_- Pcs -_- KUNINGAN';
        $seed_array = $this->multiexplode(array("\n", "\r", "\x0B"),$seed); // deleminiter must be an array
        /*
        " " (ASCII 32 (0x20)), an ordinary space.
        "\t" (ASCII 9 (0x09)), a tab.
        "\n" (ASCII 10 (0x0A)), a new line (line feed).
        "\r" (ASCII 13 (0x0D)), a carriage return.
        "\0" (ASCII 0 (0x00)), the NUL-byte.
        "\x0B" (ASCII 11 (0x0B)), a vertical tab.
        */

        // remove from array empty element
        $seed_array = array_filter($seed_array);
        foreach ($seed_array as $key => $value) {
           //echo $no . ' ' . $value . '<br>';
            $cleaned_value = trim($value);
            $this->insertToDatabase($cleaned_value);
        }
    }

    /**
     * import nasabah baru dengan existing account number and balance
     */
    public function insertToDatabase($string)
    {

        $exploded = $this->multiexplode(array("-_-"),$string); // deleminiter must be an array
        $material_code = trim($exploded[0]);
        $material_name = trim($exploded[1]);
        $unit = trim($exploded[2]);
        $material_desc = trim($exploded[3]);
        $material_type = trim($exploded[4]);
        $quantity_per_unit = trim($exploded[5]);

        if (empty($material_code)) { // material code tidak diisi, sistem menggenerate otomatis

                // create material code
                // Delimit by multiple spaces, hyphen, underscore, comma
                $words = preg_split("/[\s,_-]+/", $material_name);
                $acronym = "";
                foreach ($words as $w) {
                  $acronym .= strtoupper($w[0]);
                }
                // check apakah kode material sudah ada atau belum
                $field = "material_code";
                $value = $acronym;
                $material_code_exist = GenericModel::isExist('material_list', $field, $value);
                if ($material_code_exist) {
                    $query = "SELECT `material_code` FROM `material_list` WHERE `material_code` LIKE '%$acronym%' ORDER BY `created_timestamp` DESC LIMIT 1";
                    $max = GenericModel::rawSelect($query, false);
                    $material_number = FormaterModel::getNumberOnly($max->material_code) + 1;
                    $material_code = FormaterModel::getLetterOnly($acronym) . $material_number;
                } else {
                    // create material code
                    // Delimit by multiple spaces, hyphen, underscore, comma
                    $words = preg_split("/[\s,_-]+/", $material_name);
                    $acronym = "";
                    foreach ($words as $w) {
                      $acronym .= strtoupper($w[0]);
                    }
                    $material_code = $acronym;
                }
        }

        $insert = array(
                        'uid'    => GenericModel::guid(),
                        'material_code' => $material_code,
                        'material_name' => $material_name,
                        'material_category' => $material_category,
                        'material_type' => $material_type,
                        'quantity_per_unit' => $quantity_per_unit,
                        'unit' => $unit,
                        'material_desc' => $material_desc,
                        'creator_id'    => SESSION::get('uid')
                        );

        if (GenericModel::insert('material_list', $insert)) {
            echo " oke $material_code <br>";
        } else {
           echo $string . " : $material_code <br>";
        }
    }

   

    public function multiexplode ($delimiters,$string) // deleminiter must be an array
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

}