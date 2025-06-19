import { v4 as uuidv4 } from 'uuid';
import { encryptData, generateSignature } from './CryptoCustom';

interface MerchantInfo {
    VINNET_MERCHANT_CODE: string,
    VINNET_MERCHANT_KEY: string,
    IPSOS_PRIVATE_KEY: string,
    IPSOS_PUBLIC_KEY: string,
    VINNET_PUBLIC_KEY: string,
}

interface VinnetPost {
    merchantCode: string,
    reqUuid: string,
    reqData: string,
    sign: string,
}

const generateVinnetPostData = (postData: any, merchantInfoData: MerchantInfo): VinnetPost => {
    const uuid = uuidv4();
    const reqData = encryptData(JSON.stringify(postData), merchantInfoData.VINNET_PUBLIC_KEY);
    const signature = generateSignature(
        merchantInfoData.VINNET_MERCHANT_CODE + uuid + reqData,
        merchantInfoData.IPSOS_PRIVATE_KEY
    );

    return {
        merchantCode: merchantInfoData.VINNET_MERCHANT_CODE,
        reqUuid: uuid,
        reqData: reqData,
        sign: signature,
    };
};

export default generateVinnetPostData;