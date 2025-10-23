import { Button } from "@mui/material";
import "./ErrorPage.css";
import { Link } from "react-router-dom";
import { useNavigate, useLocation } from 'react-router-dom';



const ErrorPage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { errorCode, errorMessage } = location.state || {};

  return (
    <>
      <div className="page404">
        <div className="center">
          <div className="error">
            <div className="number">4</div>
            <div className="illustration">
              <div className="circle" />
              <div className="clip">
                <div className="paper">
                  <div className="face">
                    <div className="eyes">
                      <div className="eye eye-left" />
                      <div className="eye eye-right" />
                    </div>
                    <div className="rosyCheeks rosyCheeks-left" />
                    <div className="rosyCheeks rosyCheeks-right" />
                    <div className="mouth" />
                  </div>
                </div>
              </div>
            </div>
            <div className="number">{errorCode}</div>
          </div>
          <div className="text-404">
            {errorMessage}
          </div>
            {/* <Button
                variant="contained"
                color="primary"
                onClick={handleGoBack}
                sx={{ mt: 4 }}
            >
                GO BACK
            </Button> */}
        </div>
      </div>
    </>
  );
};

export default ErrorPage;
