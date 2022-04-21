import { Outlet } from "react-router-dom";
import './index.css';

const Layout = () => {
  return (
    <>    
      <div className="og-master-container">  
        <Outlet /> 
      </div>
    </>
  )
};

export default Layout;