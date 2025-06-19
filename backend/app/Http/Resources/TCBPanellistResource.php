<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TCBPanellistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $monthlyData = [
            [ 'month' => '2023', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Jan', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Feb', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Mar', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Apr', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'May', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Jun', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Jul', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Aug', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Sep', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Oct', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Nov', 'NewRecruitment' => 0, 'Replenishment' => 0],
            [ 'month' => 'Dec', 'NewRecruitment' => 0, 'Replenishment' => 0]
        ];

        $monthNames = ['2023', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        foreach($this->resource as $item)
        {
            $year = Carbon::createFromFormat('Y-m', $item->month)->year;
            
            if($year === 2023)
            {
                $monthIndex = 0;
            }
            else
            {
                $monthIndex =  Carbon::createFromFormat('Y-m', $item->month)->month;
            }

            $monthName = $monthNames[$monthIndex];

            foreach($monthlyData as &$md)
            {
                if($md['month'] === $monthName)
                {
                    if($item->recruitment_status === 'Replenishment')
                    {
                        $md['Replenishment'] += (int)$item->count;
                    } 
                    elseif($item->recruitment_status === 'New Recruitment') 
                    {
                        $md['NewRecruitment'] += (int)$item->count;
                    }
                }
            }
            
        }

        return array_values($monthlyData);
    }
    
}
