// utils/crypto.ts
import JSEncrypt from 'jsencrypt';
import crypto from 'crypto';
import CryptoJS from 'crypto-js';
import { KJUR, KEYUTIL } from 'jsrsasign';

/**
 * Function to generate a signature
 * @param {string} data - The data to sign
 * @param {string} privateKey - The private key to sign the data with
 * @returns {string} The base64 encoded signature
 */
export function generateSignature(data: string, privateKey: string): string {
    const sig = new KJUR.crypto.Signature({ alg: 'SHA256withRSA' });
    sig.init(privateKey);
    sig.updateString(data);
    const signature = sig.sign();
    const buffer = Buffer.from(signature, 'hex'); // Convert hex to Buffer
    return buffer.toString('base64'); // Encode Buffer to base64
}

/**
 * Function to verify a signature
 * @param {string} data - The original data
 * @param {string} signature - The base64 encoded signature to verify
 * @param {string} publicKey - The public key to verify the signature with
 * @returns {boolean} True if the signature is valid, false otherwise
 */
// export function verifySignature(data:string, signature:string, publicKey:string) {
//     const sig = new KJUR.crypto.Signature({ alg: 'SHA256withRSA' });
//     sig.init(publicKey);
//     sig.updateString(data);
//     const hexSignature = Buffer.from(signature, 'base64').toString('hex'); // Convert base64 to hex
//     return sig.verify(hexSignature); // Verify the signature
// }

// Function to encrypt data
export function encryptData(data: string, publicKey: string): string {
    const crypt = new JSEncrypt();
    crypt.setPublicKey(publicKey);
    const encrypted = crypt.encrypt(data); // Use library's encrypt method
    
    if (encrypted === false) {
        // Handle encryption failure (e.g., log error, throw exception)
        console.error('Encryption failed');
    return ''; // Or return another default value
    }
    return encrypted; // Encrypted data format depends on the library
}

// Function to decrypt data
export function decryptData(encryptedData: string, privateKey: string): string {
    const crypt = new JSEncrypt();
    crypt.setPrivateKey(privateKey);
    const decrypted = crypt.decrypt(encryptedData); // Use library's decrypt method

    if (decrypted === false) {
        // Handle encryption failure (e.g., log error, throw exception)
        console.error('Encryption failed');
        return ''; // Or return another default value
    }
    return decrypted; // Decrypted data format depends on the library
}





