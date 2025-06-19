import AboutPage from "../pages/TechcombankPanel/MarketTrend";
import Engagements from "../pages/TechcombankPanel/Engagements";
import Panellist from "../pages/TechcombankPanel/Panellist";
import Products from "../pages/TechcombankPanel/Products";

export const TechcombankRoutes = [
    {
        path: "/techcombank/dashboard",
        component: Panellist
    },
    {
        path: "/techcombank/products",
        component: Products
    },
    {
        path: "techcombank/engagements",
        component: Engagements
    },
    {
        path: "techcombank/about",
        component: AboutPage
    }
];
