import React, { useEffect, useRef } from 'react';
import * as d3 from 'd3';

export interface ChartData {
  name: string;
  value: number;
}

export interface StackedBarChartData {
  category: string;
  attributes: ChartData[];
}

type StackData = { [key: string]: number };

interface SeriesPoint {
  data: StackData;
  index: number;
  key: string;
}

interface StackPoint {
  index: number;
  data: { [key: string]: number };
  key: string;
}

type DataPoint = d3.SeriesPoint<{ [key: string]: number }>;

interface StackedBarChartPercentageProps {
  legend_title: string;
  data: StackedBarChartData[];
  width?: number;
  height?: number;
}

const COLORS = [
  'var(--chart-color-primary)',
  'var(--chart-color-secondary)', 
  'var(--chart-color-third)', 
  'var(--chart-color-forth)', 
  'var(--chart-color-fifth)', 
  'var(--chart-color-sixth)',
  'var(--chart-color-seventh)',
  'var(--chart-color-eighth)',
];

const FONT_COLORS = [
  'var(--chart-font-color-primary)',
  'var(--chart-font-color-secondary)', 
  'var(--chart-font-color-third)', 
  'var(--chart-font-color-forth)', 
  'var(--chart-font-color-fifth)', 
  'var(--chart-font-color-sixth)',
  'var(--chart-font-color-seventh)',
  'var(--chart-font-color-eighth)',
];

const StackedBarChartPercentageComponent: React.FC<StackedBarChartPercentageProps> = ({ legend_title, data }) => {
  const svgRef = useRef<SVGSVGElement | null>(null);
  const containerRef = useRef<HTMLDivElement>(null);
  const legendRef = useRef<HTMLTableElement | null>(null);
  const tooltipRef = useRef<HTMLDivElement | null>(null); // Tooltip reference

  useEffect(() => {
    if (!svgRef.current || !containerRef.current || !legendRef.current)  return;

    if (!data || data.length === 0) {
      console.error("Data is undefined or empty");
      return;
    }
    
    const svg = d3.select(svgRef.current);
    const width = svg.node()?.clientWidth || 0;
    const height = svg.node()?.clientHeight || 0;
    
    const margin = { top: 20, right: 40, bottom: 20, left: 40 };
    const innerWidth = width - margin.left - margin.right;
    const innerHeight = height - margin.top - margin.bottom;
    
    // Create scales
    const xScale = d3.scaleBand()
      .domain(data.map(d => d.category))
      .range([0, innerWidth])
      .padding(0.1);

    const yScale = d3.scaleLinear()
      .domain([0, 100])
      .range([innerHeight, 0]);

    const colorScale = d3.scaleOrdinal<string>()
      .domain(data[0].attributes.map(attr => attr.name))
      .range(COLORS);

    // Stack the data
    const stack = d3.stack<StackData>()
      .keys(data[0].attributes.map(attr => attr.name));
      
    const transformedData = data.map(d => {
      const result: StackData = {};
      const total = d.attributes.reduce((sum, attr) => sum + attr.value, 0);

      d.attributes.forEach(attr => {
        result[attr.name] = (attr.value / total * 100);
      });

      return result;
    });

    const series = stack(transformedData);

    // Create the chart
    svg.selectAll('*').remove();

    const chartGroup = svg.append('g')
      .attr('transform', `translate(${margin.left},${margin.top})`);

    // Tooltip
    const tooltip = d3.select(tooltipRef.current)
      .style('position', 'absolute')
      .style('background-color', '#fff')
      .style('border', '1px solid #ccc')
      .style('padding', '5px')
      .style('border-radius', '3px')
      .style('pointer-events', 'none')
      .style('opacity', 0)

    // Append a group for each stack layer
    const layers = chartGroup.selectAll('g.layer')
      .data(series)
      .enter().append('g')
      .attr('class', 'layer')
      .attr('fill', d => colorScale(d.key));

    // Append rectangles within each group
    layers.selectAll('rect')
      .data(d => d as d3.SeriesPoint<{ [key: string]: number }>[])  // Explicitly type each point
      .enter().append('rect')
      .attr('x', (d, i) => xScale(data[i].category)!)
      .attr('y', d => yScale(d[1]))
      .attr('height', d => yScale(d[0]) - yScale(d[1]))
      .attr('width', xScale.bandwidth())
      .on('mouseover', (event, d) => {
        tooltip.transition().duration(200).style('opacity', 1);

        const layer = d3.select(event.currentTarget.parentNode as SVGGElement);
        const seriesData = layer.datum() as d3.Series<{ [key: string]: number }, string>;
        const key = seriesData.key;
        const value = d.data[key];
        const roundedValue = Math.round(value);

        tooltip.text(`${key}: ${roundedValue}%`);
      })
      .on('mousemove', event => {
        const container = containerRef.current!;
        const [offsetX, offsetY] = d3.pointer(event, container);

        tooltip
          .style('left', `${offsetX + 10}px`)
          .style('top', `${offsetY - 40}px`);
      })
      .on('mouseout', () => {
        tooltip.transition().duration(200).style('opacity', 0);
      });
    
    // Add text labels on top of bars
    layers.selectAll('text')
      .data(d => d as d3.SeriesPoint<{ [key: string]: number }>[])  // Explicitly type each point
      .enter().append('text')
      .attr('x', (d, i) => xScale(data[i].category)! + xScale.bandwidth() / 2)
      .attr('y', d => yScale(d[0]) - 15) // Offset by 15 pixels for visibility
      .attr('text-anchor', 'middle')
      .attr('font-size', '12px')
      .attr('fill', 'white')
      .text((d, i, nodes) => {
        // Get the key from the parent layer
        const layer = d3.select(nodes[i].parentNode as SVGGElement);
        const seriesData = layer.datum() as d3.Series<{ [key: string]: number }, string>;
        const key = seriesData.key;
        const value = d.data[key];
        const roundedValue = Math.round(value);
        return `${roundedValue}%`;
      });

    // Add axes
    chartGroup.append('g')
      .attr('transform', `translate(0,${innerHeight})`)
      .call(d3.axisBottom(xScale));

    chartGroup.append('g')
      .call(d3.axisLeft(yScale).ticks(5).tickFormat(d => `${d}%`));

    const legend = d3.select(legendRef.current);

    // Clear existing rows in <tbody> but keep <thead>
    legend.select('thead').selectAll('tr').remove();
    legend.select('tbody').selectAll('tr').remove();

    // Create the table header based on attributes
    const headers = ['Category', ...data[0].attributes.map(attr => attr.name)];
    const headerRow = legend.select('thead')
      .append('tr');

    headerRow.selectAll('th')
      .data(headers)
      .enter().append('th')
      .text(d => d);

    const rows = legend.select('tbody')
      .selectAll('tr')
      .data(data)
      .enter().append('tr');

    rows.each(function(d, i) {
      const row = d3.select(this);
      row.append('td')
        .text(d.category);

      d.attributes.forEach((attr) => {
        row.append('td')
          .style('text-align', 'center')
          .text(attr.value);
      });
    });
        
  }, [data]);

  return (
    <div className='widget-col-item'>
        {/* <div className='widget-item'>
            <h3 style={{ color: 'var(--font-fifth)'}}>{title}</h3>
        </div> */}
        <div ref={containerRef} className="chart-container widget-item">
            <svg ref={svgRef} width="100%" height="400" />
            <div ref={tooltipRef} className="tooltip"></div>
        </div>
        
        <table ref={legendRef} className='legend-table'>
            <thead>
                <tr>
                    <th>{legend_title}</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
  ) 
};

export default StackedBarChartPercentageComponent;
