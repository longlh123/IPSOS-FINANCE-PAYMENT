import "../../assets/css/components.css";
import ITelecomLogo from "../../assets/img/providers/itelecom_logo.png";
import MobiFoneLogo from "../../assets/img/providers/mobifone_logo.png";
import VietnameMobileLogo from "../../assets/img/providers/vietnamobile_logo.png";
import ViettelLogo from "../../assets/img/providers/viettel_logo.png";
import VinaphoneLogo from "../../assets/img/providers/vinaphone_logo.png";
import WintelLogo from "../../assets/img/providers/wintel_logo.png";
import GmobileLogo from "../../assets/img/providers/gmobile_logo.svg";
import VinnetJSC from "../../assets/img/providers/Vinnet JSC.png";
import React, { useState, useCallback, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from "axios";
import GuestLayout from '../../Layouts/GuestLayout';
import LoadingButton from '@mui/lab/LoadingButton';
import SendIcon from '@mui/icons-material/Send';
import { ApiConfig } from '../../config/ApiConfig';

import {
    Card,
    CardContent,
    Divider,
    FormControl,
    InputLabel,
    ListItemText,
    MenuItem,
    Select,
    SelectChangeEvent,
    TextField,
} from "@mui/material";
import Alert from "@mui/material/Alert";
import { getServiceCode, getServiceCodeForMobileCardPurchaseService } from "../../utils/VinnetFunctions";
import ConfirmDialog from "../../components/Dialogs/ConfirmDialog";
import useDialog from "../../hook/useDialog";
import RejectDialog from "../../components/Dialogs/RejectDialog";

interface Provider {
    label: string,
    serviceCode: string,
    imgSrc: string
}

type ServiceType = 'topup' | 'card';

const MobileTopUpServices: Provider[] = [
    { label: 'Viettel', serviceCode: 'S0002', imgSrc: ViettelLogo },
    { label: 'MobiFone', serviceCode: 'S0003', imgSrc: MobiFoneLogo },
    { label: 'Vinaphone', serviceCode: 'S0028', imgSrc: VinaphoneLogo },
    { label: 'Vietnamobile', serviceCode: 'S0029', imgSrc: VietnameMobileLogo },
    { label: 'Gmobile', serviceCode: 'S0030', imgSrc: GmobileLogo },
    { label: 'Wintel', serviceCode: 'S0031', imgSrc: WintelLogo },
    { label: 'I-Telecom', serviceCode: 'S0033', imgSrc: ITelecomLogo },
];

const MobileCardPurchaseServices: Provider[] = [
    { label: 'Viettel', serviceCode: 'S0004', imgSrc: ViettelLogo },
    { label: 'MobiFone', serviceCode: 'S0012', imgSrc: MobiFoneLogo },
    { label: 'Vinaphone', serviceCode: 'S0014', imgSrc: VinaphoneLogo },
    { label: 'Vietnamobile', serviceCode: 'S0013', imgSrc: VietnameMobileLogo },
    { label: 'Gmobile', serviceCode: 'S0011', imgSrc: GmobileLogo },
    { label: 'Wintel', serviceCode: 'S0015', imgSrc: WintelLogo },
    { label: 'I-Telecom', serviceCode: 'S0014', imgSrc: ITelecomLogo },
];

const MobileServices: Record<ServiceType, Provider[]> = {
    topup: MobileTopUpServices,
    card: MobileCardPurchaseServices
};

const isValidServiceType = (type: any): type is ServiceType => {
    return type === 'topup' || type === 'card';
};

const SubmitPhoneNumber = () => {
    const navigate = useNavigate();

    const { open, openDialog, closeDialog } = useDialog();
    const [ shouldSubmit, setShouldSubmit] = useState<boolean>(false);
    const [ messageConfirm, setMessageConfirm ] = useState<string>("");
    const [ phoneNumber, setPhoneNumber] = useState('');
    const [ loadingConfirmed, setLoadingConfirmed] = useState(false);
    const [ loadingReject, setLoadingReject] = useState(false);
    const [ isError, setIsError] = useState(false);
    const [ statusMessage, setStatusMessage] = useState('');

    const [ selectedProvider, setSelectedProvider] = useState('');
    const [ selectedServiceCode, setSelectedServiceCode] = useState('');
    const [ isManualProviderSelection, setIsManualProviderSelection] = useState(false);
    
    const [ isRejectDialogOpen, setIsRejectDialogOpen] = useState(false);
    const [ rejectReason, setRejectReason] = useState('');

    const { serviceType, url } = useParams<{
        serviceType?: string,
        url?: string
    }>();

    const providers: Provider[] = isValidServiceType(serviceType) ? MobileServices[serviceType] : [];

    const getSelectedProvider = (
        serviceType: 'topup' | 'card',
        providerLabel: string
    ) => {
        const serviceList = MobileServices[serviceType];
        const provider = serviceList.find(p => p.label === providerLabel);
        return provider;
    };

    const handleChangeProvider = (event: SelectChangeEvent<string>) => {
        const providerLabel = event.target.value as string;

        setSelectedProvider(event.target.value);
        setIsManualProviderSelection(true);
        
        // Find the provider object based on the selected label
        if(isValidServiceType(serviceType)){
            const selectedProviderObj = getSelectedProvider(serviceType, providerLabel)

            console.log('Found selectedProviderObj:', selectedProviderObj);
        
            // Set the corresponding serviceCode
            if (selectedProviderObj) {
                console.log('Setting serviceCode to:', selectedProviderObj.serviceCode);
                console.log('Previous selectedServiceCode:', selectedServiceCode);

                setSelectedServiceCode(selectedProviderObj.serviceCode);

                // Verify the change (này sẽ không hiện ngay vì state update là async)
                console.log('selectedServiceCode after setState (might not reflect immediately):', selectedServiceCode);
            } else {
                console.log('selectedProviderObj not found!');
            }
        } else {
            console.error(`Invalid serviceType: ${serviceType}`);
        }
    };

    // Thêm useEffect để theo dõi sự thay đổi của selectedServiceCode
    useEffect(() => {
        console.log('selectedServiceCode changed to:', selectedServiceCode);
    }, [selectedServiceCode]);

    // Cũng có thể debug providers array để đảm bảo structure đúng
    useEffect(() => {
        console.log('Current providers structure:', providers);
        console.log('Sample provider:', providers[0]); // kiểm tra structure của phần tử đầu tiên
    }, [serviceType]);

    const handleChangeInput = (value: string) => {
        setPhoneNumber(value)
        setStatusMessage('');
        setIsError(false);

        let serviceCode = "";

        switch (serviceType) {
            case "topup":
                serviceCode = getServiceCode(value) ?? "";
                break;
            case "card":
                serviceCode = getServiceCodeForMobileCardPurchaseService(value) ?? "";
                break;
            default:
                console.warn(`Invalid serviceType: ${serviceType}`);
        }

        // const mobiFoneServiceCodes = ["S0003", "S0012"];
        
        // if(mobiFoneServiceCodes.includes(serviceCode)){
        //     setStatusMessage("Nhà mạng MobiFone đang gặp sự cố, vui lòng dùng số điện thoại khác nếu có hoặc PVV có thể gửi quà trực tiếp cho bạn.");
        //     setIsError(true);
        //     setSelectedProvider("");   // reset provider
        //     setSelectedServiceCode(""); // reset service
        //     return;
        // }    

        if (serviceCode) {
            const provider = providers.find((p) => p.serviceCode === serviceCode);

            if (provider) {
                setSelectedProvider(provider.label);
                setSelectedServiceCode(provider.serviceCode);
            }
        } else {
            setSelectedProvider('');
            setSelectedServiceCode('');
        }
    }
    
    const handleSubmitPhoneNumber = useCallback(async () => {

        setStatusMessage('');
        setIsError(false);

        if(phoneNumber.length == 0)
        {
            setStatusMessage('Vui lòng nhập số điện thoại của bạn.');
            setIsError(true);
            setLoadingConfirmed(false);
            setShouldSubmit(false);
            return;
        }
        
        const serviceCode = getServiceCode(phoneNumber);

        if(!serviceCode){
            setStatusMessage('Số điện thoại không đúng. Vui lòng kiểm tra lại.');
            setIsError(true);
            setLoadingConfirmed(false);
            setShouldSubmit(false);
            return;
        };

        setLoadingConfirmed(true);

        try
        {
            const reqAuthenPost = await axios.post(ApiConfig.vinnet.performMultipleTransactions, {
                url: url,
                service_type: serviceType,
                phone_number: phoneNumber,
                service_code: selectedServiceCode,
            }, { 
                headers : {
                    'Content-Type': 'application/json'
                }
            });

            console.log(reqAuthenPost.data);

            if(reqAuthenPost.status === 200){
                navigate('/page200?message=' + reqAuthenPost.data.message);
            } else {
                throw new Error(reqAuthenPost.data.message);
            }
        } catch (error) {
            setIsError(true);

            let errorMessage = '';

            if (axios.isAxiosError(error)) {
                errorMessage = error.response?.data.message ?? error.message;
            } else {
                errorMessage = (error as Error).message;
            }

            setStatusMessage(errorMessage);
            console.error('Error:', errorMessage);
        } finally
        {
            setLoadingConfirmed(false);
            setShouldSubmit(false);
        }
        
    }, [url, phoneNumber, selectedServiceCode]);
    
    const handleFormSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        setMessageConfirm("Chúng tôi sẽ gửi phần quà vào số điện thoại " + phoneNumber + " (" + selectedProvider + "). Vui lòng xác nhận thông tin này một lần nữa?")
        openDialog();
    };

    const handleConfirm = () => {
        setShouldSubmit(true);
        closeDialog();
    };

    useEffect(() => {
        if (shouldSubmit) {
            handleSubmitPhoneNumber();
        }
    }, [shouldSubmit, handleSubmitPhoneNumber]);
    
    const handleOpenRejectDialog = () => {
        setIsRejectDialogOpen(true);
    };

    const handleCloseRejectDialog = () => {
        setIsRejectDialogOpen(false);
    };

    const handleConfirmReject = async (reason: string) => {
        
        try
        {
            setStatusMessage('');
            setIsError(false);
            setLoadingReject(true);

            setRejectReason(reason);
            console.log('Rejection Reason:', reason);

            const request = await axios.post(ApiConfig.vinnet.rejectTransaction, {
                url: url,
                reject_message: reason
            }, { 
                headers : {
                    'Content-Type': 'application/json'
                }
            });

            console.log(request.data);
            navigate('/page200?message=' + request.data.message);
        } 
        catch (error) 
        {
            setIsError(true);

            let errorMessage = '';

            if (axios.isAxiosError(error)) {
                errorMessage = error.response?.data.message ?? error.message;
            } else {
                errorMessage = (error as Error).message;
            }

            setStatusMessage(errorMessage);
            console.error('Error:', errorMessage);
        } 
        finally
        {
            setIsRejectDialogOpen(false);
            setLoadingReject(false);
        }
    };

    return (
        <GuestLayout>
            <Card className="welcome-container">
                <CardContent className='welcome-header'>
                    
                </CardContent>
                <CardContent className='welcome-body'>
                    <div className='item'>
                        <p>Xin chân thành cảm ơn bạn đã dành thời gian quý báu để hoàn thành khảo sát của chúng tôi. Ý kiến đóng góp của bạn là nguồn cảm hứng quan trọng để chúng tôi không ngừng cải thiện chất lượng sản phẩm/dịch vụ.</p>
                    </div>
                    <div className='item'>
                        <p>Để bày tỏ lòng biết ơn, chúng tôi xin gửi tặng bạn một phần quà nhỏ. Vui lòng điền số điện thoại của bạn vào ô bên dưới để chúng tôi có thể liên hệ và gửi quà đến bạn sớm nhất.</p>
                    </div>
                    <div className='item'>
                        <p><b>Chúng tôi cam kết bảo mật tuyệt đối thông tin cá nhân của bạn</b>. Số điện thoại bạn cung cấp chỉ được sử dụng để liên hệ trao quà và sẽ không được chia sẻ với bất kỳ bên thứ ba nào.</p>
                    </div>
                    <div className='item'>
                        <p>Một lần nữa, xin chân thành cảm ơn!</p>
                    </div>
                    {isError && (
                        <div className='item'>
                            <Alert severity="error" className="message-invalid">{statusMessage}</Alert> 
                        </div>
                    )}
                    <form onSubmit={handleFormSubmit}>
                        <div className='item'>
                            <TextField
                                label="Nhập số điện thoại"
                                variant="outlined"
                                onChange={(e) => handleChangeInput(e.target.value)}
                                fullWidth
                            />
                        </div>
                        <br/>
                        <div className='item'>
                            <FormControl fullWidth>
                                <InputLabel id="provider-select-label">Thay đổi nhà mạng</InputLabel>
                                <Select
                                    labelId="provider-select-label"
                                    id="provider-select"
                                    value={selectedProvider}
                                    label="Thay đổi nhà mạng"
                                    onChange={handleChangeProvider}
                                    renderValue={(value) => value}
                                    disabled = {!selectedServiceCode}
                                >
                                    <MenuItem value="">
                                        <em className="provider-warning">Vui lòng chỉ thay đổi nhà mạng khi bạn đã đăng ký giữ số chuyển mạng</em>
                                    </MenuItem>
                                    {providers.map((provider) => (
                                    <MenuItem key={provider.label} value={provider.label}>
                                        <div style={{display: 'flex', flexDirection: 'row', alignItems: 'center', justifyItems: 'center', gap: 20, width: '100%'}}>
                                            <div>
                                                <img src={provider.imgSrc} width={50} height={50}  />
                                            </div>
                                            <div>
                                                <ListItemText primary={provider.label} />
                                            </div>
                                        </div>
                                        
                                        
                                    </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        </div>
                        <br/>
                        <div className="action">
                            <LoadingButton
                                type="submit"
                                size="large"
                                endIcon={<SendIcon />}
                                loading={loadingConfirmed}
                                loadingPosition="end"
                                variant="contained"
                                className='btn bg-vinnet-primary'
                                >
                                <span>XÁC NHẬN</span>
                            </LoadingButton>
                            {/* <LoadingButton
                                type="button"
                                size="large"
                                endIcon={<ThumbDownOffAltRoundedIcon />}
                                loading={loadingReject}
                                onClick={handleOpenRejectDialog}
                                loadingPosition="end"
                                variant="contained"
                                className='btn bg-reject'
                                >
                                <span>TỪ CHỐI</span>
                            </LoadingButton> */}
                        </div>   
                    </form>
                </CardContent>
                <Divider/>
                <CardContent className='welcome-footer'>
                    <img src={VinnetJSC}/>
                    <div className="vinnet-slogan">Giữ an toàn cho thông tin của bạn là trách nhiệm của chúng tôi.</div>
                </CardContent>
            </Card>
            
            {/* Show ConfirmDialog */}
            <ConfirmDialog
                open={open}
                onClose={closeDialog}
                onConfirm={handleConfirm}
                title="Xác Nhận"
                message={messageConfirm}
            />

            {/* Show RejectDialog */}
            <RejectDialog
                open={isRejectDialogOpen}
                onClose={handleCloseRejectDialog}
                onConfirm={handleConfirmReject}
                title="Lý do từ chối"
                message="Vui lòng nhập lý do từ chối tại đây"
            />
        </GuestLayout>
    );
};

export default SubmitPhoneNumber;
