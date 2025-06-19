import '../../../assets/css/techcombank.css';
import MarketTrend_1 from '../../../assets/img/market_trend/market_trend_1.png';
import MarketTrend_2 from '../../../assets/img/market_trend/market_trend_2.png';
import MarketTrend_3 from '../../../assets/img/market_trend/market_trend_3.png';
import MarketTrend_4 from '../../../assets/img/market_trend/market_trend_4.png';
import MarketTrend_5 from '../../../assets/img/market_trend/market_trend_5.png';
import Page2Trend_1 from '../../../assets/img/market_trend/page2_trend_1.png';
import Page2Trend_2 from '../../../assets/img/market_trend/page2_trend_2.png';
import Page2Trend_3 from '../../../assets/img/market_trend/page2_trend_3.png';
import Page2Trend_4 from '../../../assets/img/market_trend/page2_trend_4.png';
import Page2Trend_5 from '../../../assets/img/market_trend/page2_trend_5.png';
import Page2Trend_6 from '../../../assets/img/market_trend/page2_trend_6.png';
import Page2Trend_7 from '../../../assets/img/market_trend/page2_trend_7.png';

const MarketTrendWidget:React.FC = () => {

    return (
        <>
            <div className='market-trend-container'>
                <div style={{textAlign: 'center', padding: 10, color: 'var(--font-secondary)'}}>
                    <div style={{paddingTop: 20, paddingBottom: 20}}>
                        <h1>Empower the <span style={{fontSize: 45, color: 'var(--font-third)'}}>1st</span> <br/>customer-panel in Vietnam</h1>
                    </div>
                    <div style={{fontSize: 26, fontWeight: 400, paddingTop: 20, paddingBottom: 20}}>
                        5 Things You Should Know
                    </div>
                </div>
                <div className='market-trend-item'>
                    <div className='tcb-content left'>
                        <div className='title'>
                            <h4>01. Fast & Cost-Effective</h4>
                        </div>
                        <div className='text'>
                            You can get results quickly and affordably compared to traditional research methods.
                        </div>
                    </div>
                    <img src={MarketTrend_1} width={250} height={230} style={{position: 'absolute', right: '-50px'}} />
                </div>
                <div className='market-trend-item'>
                    <img src={MarketTrend_2} width={250} height={230} style={{position: 'absolute', left: '-50px'}} />
                    <div className='tcb-content right'>
                        <div className='title'>
                            <h4>02. Targeted Insights</h4>
                        </div>
                        <div className='text'>
                            Recruit specific demographics and psychographics to match your target audience for more relevant results.
                        </div>
                    </div>
                </div>
                <div className='market-trend-item'>
                    <div className='tcb-content left'>
                        <div className='title'>
                            <h4>03. High Engagement & Response Rates</h4>
                        </div>
                        <div className='text'>
                            Building a community fosters higher engagement and response rates, especially when managed well and incentives are provided.
                        </div>
                    </div>
                    <img src={MarketTrend_3} width={250} height={230} style={{position: 'absolute', right: '-50px'}} />
                </div>
                <div className='market-trend-item'>
                    <img src={MarketTrend_4} width={250} height={230} style={{position: 'absolute', left: '-50px'}} />
                    <div className='tcb-content right'>
                        <div className='title'>
                            <h4>04. Track Trends & Changes</h4>
                        </div>
                        <div className='text'>
                            Longitudinal studies with the same panel allow you to track consumer sentiment, brand perception, and emerging trends over time.
                        </div>
                    </div>
                </div>
                <div className='market-trend-item'>
                    <div className='tcb-content left'>
                        <div className='title'>
                            <h4>05. Improved Product Development & Marketing</h4>
                        </div>
                        <div className='text'>
                            Gathering feedback on prototypes, marketing campaigns, or new product ideas in a cost-effective way leads to more informed decisions.
                        </div>
                    </div>
                    <img src={MarketTrend_5} width={250} height={230} style={{position: 'absolute', right: '-50px'}} />
                </div>
            </div>
            <div className='market-trend-container'>
                <div className='row'>
                    <div style={{paddingTop: 20, paddingBottom: 20, textAlign: 'center', width: '100%'}}>
                        <h1>Empower the <span style={{fontSize: 45, color: 'var(--font-third)'}}>1st</span> <br/>customer-panel in Vietnam</h1>
                    </div>
                </div>
                <div className='row'>
                    <div className='cell' style={{height: '400px'}}>
                        <div className='title'>Overall Panel Growth</div>
                        <div>The recruitment process was carried out over two years, from 2023 to 2024, during which the community <b>expanded from 500 to 1000 members</b>.</div>
                        <div className='logo'>
                            <img src={Page2Trend_2}></img>
                        </div>
                    </div>
                    <div className='cell' style={{height: '400px'}}>
                        <div className='title'>Speech</div>
                        <div>With a remarkable <b>turnaround time of 10 hours for 200 samples</b> and the capacity to manage <b>large-scale surveys with a 30-40% response rate</b>, they are efficient and cost-effective.</div>
                        <div className='logo'>
                            <img src={Page2Trend_1}></img>
                        </div>
                    </div>
                </div>
                <div className='row'>
                    <div className='cell full-width' style={{height: '150px'}}>
                        <img src={Page2Trend_3}></img>
                    </div>
                </div>
                <div className='row'>
                    <div className='cell' style={{height: '500px'}}>
                        <div className='title'>Age segment</div>
                        <div className='logo'>
                            <img src={Page2Trend_4}></img>
                        </div>
                    </div>
                    <div className='cell' style={{height: '500px'}}>
                        <div className='title'>Occupation Segments</div>
                        <div className='logo'>
                            <img src={Page2Trend_5}></img>
                        </div>
                    </div>
                </div>
                <div className='row'>
                    <div className='cell' style={{height: '450px'}}>
                        <div className='title'>Age segment</div>
                        <div className='logo'>
                            <img src={Page2Trend_6}></img>
                        </div>
                    </div>
                    <div className='cell' style={{height: '450px'}}>
                        <div className='title'>Occupation Segments</div>
                        <div className='logo'>
                            <img src={Page2Trend_7}></img>
                        </div>
                    </div>
                </div>
            </div> 
        </>
    );
}

export default MarketTrendWidget;