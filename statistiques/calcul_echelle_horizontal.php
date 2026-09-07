<?php
				if ($max>100000) 
				{
					$premiere_grad=round(($max/16),-4,PHP_ROUND_HALF_UP);
				}
				else if ($max>10000) 
				{
					$premiere_grad=round(($max/16),-3,PHP_ROUND_HALF_UP);
				}
				else if ($max>1000) 
				{
					$premiere_grad=round(($max/16),-2,PHP_ROUND_HALF_UP);
				}
				else if ($max>100) 
				{
					$premiere_grad=round(($max/16),-1,PHP_ROUND_HALF_UP);
				}
				else if ($max>10) 
				{
					$premiere_grad=round(($max/16),0,PHP_ROUND_HALF_UP);
				}
				else if ($max>1) 
				{
					$premiere_grad=1;
				}
		?>