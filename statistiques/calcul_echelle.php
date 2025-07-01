<?php
				if ($max>100000) 
				{
					$premiere_grad=round(($max/6),-4,PHP_ROUND_HALF_UP);
				}
				else if ($max>10000) 
				{
					$premiere_grad=round(($max/6),-3,PHP_ROUND_HALF_UP);
				}
				else if ($max>1000) 
				{
					$premiere_grad=round(($max/6),-2,PHP_ROUND_HALF_UP);
				}
				else if ($max>100) 
				{
					$premiere_grad=round(($max/6),-1,PHP_ROUND_HALF_UP);
				}
				else if ($max>10) 
				{
					$premiere_grad=round(($max/6),0,PHP_ROUND_HALF_UP);
				}
				else if ($max>1) 
				{
					$premiere_grad=1;
				}
		?>