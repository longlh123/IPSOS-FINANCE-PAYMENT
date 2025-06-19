import React, { useRef, useEffect } from 'react';
import * as d3 from 'd3';

interface ProgressBarChartProps {
    title: string
    data: { name: string; value: number }[]
} 

const ProgressBarChartComponent: React.FC<ProgressBarChartProps> = ({ title, data }) => {
    const svgRef = useRef<SVGSVGElement>(null);
    const containerRef = useRef<HTMLDivElement>(null);
    
    const COLORS = [
        'var(--chart-color-secondary)',
        'var(--chart-color-sixth)'
    ];

    const FONT_COLORS = [
        'var(--chart-font-color-primary)',
        'var(--chart-font-color-secondary)'
    ];
      
    useEffect(() => {
        if (!svgRef.current || !containerRef.current) return;

        const svg = d3.select(svgRef.current);
        const width = containerRef.current.offsetWidth;
        const height = 80;
        const margin = { top: 20, right: 20, bottom: 20, left: 20 };
        //const borderRadius = 10; // Border radius for rounded corners

        svg.attr('width', width)
            .attr('height', height);

        const total = data.reduce((sum, item) => sum + item.value, 0);

        const xScale = d3.scaleLinear()
            .domain([0, total])
            .range([margin.left, width - margin.right]);


        svg.selectAll('*').remove(); // Clear existing content

        let x = margin.top;
        let y = 50;

        data.map((item, index) => {
            
            const barWidth = (width - margin.top * 2) * (item.value / total);
            // const isFirstBar = index === 0;
            // const isLastBar = index === data.length - 1;

            svg.append('rect')
                .attr('x', x)
                .attr('y', margin.top)
                .attr('width', barWidth)
                .attr('height', height - margin.top - margin.bottom)
                // .attr('rx', isFirstBar ? borderRadius : 0) // Set the border radius
                // .attr('ry', isFirstBar ? borderRadius : 0) // Set the border radius
                .attr('fill', COLORS[index]);

            svg.append('text')
                .attr('x', x + barWidth / 2)
                .attr('y', (height / 2) + 5) 
                .attr('text-anchor', 'middle')
                .attr('fill', FONT_COLORS[index])
                .text(`${item.name}: ${Math.round((Math.fround((item.value / total) * 100)) * 10) / 10}%`);

            x += barWidth;
        });
    }, [data]);

    return (
        <div className='widget-row-item'>
            <div style={{width: '20%'}}>
                <h3 style={{ color: 'var(--font-secondary)'}}>{title}</h3>
            </div>
            <div ref={containerRef} className="chart-container">
                <svg ref={svgRef}></svg>
            </div>
        </div>
    );
};

export default ProgressBarChartComponent;
