import "../Directional/Directional.css";
import ArrowRightIcon from "@mui/icons-material/ArrowRight";

interface DirectionalProps {
  title: string;
  page_form?: string;
  page_to?: string;
}
const Directional: React.FC<DirectionalProps> = ({ title, page_form, page_to }) => {
  return (
    <>
      <div className="directional">
        <div className="directional-left">
          <h3>{title}</h3>
        </div>
        <div className="directional-right">
          {/* {(page_form?.length > 0 && ({page_form} <ArrowRightIcon /> {page_to})) }
           */}
          {/* {text2 && (
            <>
              <ArrowRightIcon /> {text2}
            </>
          )} */}
        </div>
      </div>
    </>
  );
};

export default Directional;
