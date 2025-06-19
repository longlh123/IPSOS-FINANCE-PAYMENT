export function getServiceCode(phonenumber: string) : (string | null){
    if(!phonenumber.trim()){
        return null;
    } else if(phonenumber.length < 10 || phonenumber.length > 11){
        return null;
    } else {
        const providers: { [key: string]: { providerCode: string; subscriberNumberPrefix: string[]; serviceCode: string } } = {
            "Viettel": {
                "providerCode": "VTT",
                "subscriberNumberPrefix": ['086', '096', '097', '098', '0162', '0163', '0164', '0165', '0166', '0167', '0168', '0169', '032', '033', '034', '035', '036', '037', '038', '039'],
                "serviceCode" : "S0002"
            },
            "Vinaphone": {
                "providerCode": "VNP",
                "subscriberNumberPrefix": ['091', '094', '088', '0123', '0124', '0125', '0127', '0129', '083', '084', '085', '081', '082'],
                "serviceCode" : "S0028"
            },
            "MobiFone": {
                "providerCode": "VMS",
                "subscriberNumberPrefix": ['089', '090', '093', '0120', '0121', '0122', '0126', '0128', '070', '079', '077', '076', '078'],
                "serviceCode" : "S0003"
            },
            "Vietnamobile": {
                "providerCode": "VNM",
                "subscriberNumberPrefix": ['052', '092', '0186', '0188', '056', '058'],
                "serviceCode" : "S0029"
            },
            "Gmobile": {
                "providerCode": "BEE",
                "subscriberNumberPrefix": ['099', '0199', '059'],
                "serviceCode" : "S0030"
            },
            "Wintel" : {
                "providerCode" : "WTL",
                "subscriberNumberPrefix" : ['055'],
                "serviceCode" : "S0031"
            },
            "I-Telecom": {
                "providerCode": "I-Telecom",
                "subscriberNumberPrefix": ['087'],
                "serviceCode" : "S0033"
            }
        };

        const prefix = phonenumber.substring(0, (phonenumber.length == 11 ? 4 : 3)); // Extract the first three digits

        for (const provider in providers) {
            const prefixes = providers[provider].subscriberNumberPrefix;
            if (prefixes.includes(prefix)) {
                return providers[provider].serviceCode;
            }
        }

        return null; // If no match is found
    }
}