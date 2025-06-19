import { Button } from "@mui/material";
import "../Page404/Page404.css";
import { Link } from "react-router-dom";
import { useNavigate, useLocation } from 'react-router-dom';

interface Page404Props {
  errMessage: string
}

const Page404: React.FC<Page404Props> = ({errMessage}) => {
  const navigate = useNavigate();
  const location = useLocation();
  const handleGoBack = () => {
    navigate('/');
  };

  // Parse the query string to get the error message
  const searchParams = new URLSearchParams(location.search);
  const errorMessage = searchParams.get('message') || errMessage;

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
            <div className="number">4</div>
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

export default Page404;
