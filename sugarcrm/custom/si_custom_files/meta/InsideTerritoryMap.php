<?php
require_once('custom/si_custom_files/meta/StateAbbreviationMap.php');

$stateToRegionMap = array(
						'AL'	=>	'SOUTHEAST',
						'AK'	=>	'CENTRAL',
						'AZ'	=>	'WEST',
						'AR'	=>	'CENTRAL',
						'CA'	=>	'WEST',
						'CO'	=>	'WEST',
						'CT'	=>	'NORTHEAST',
						'DE'	=>	'NORTHEAST',
						'DC'	=>	'NORTHEAST',
						'FL'	=>	'SOUTHEAST',
						'GA'	=>	'SOUTHEAST',
						'HI'	=>	'WEST',
						'ID'	=>	'WEST',
						'IL'	=>	'CENTRAL',
						'IN'	=>	'SOUTHEAST',
						'IA'	=>	'CENTRAL',
						'KS'	=>	'CENTRAL',
						'KY'	=>	'SOUTHEAST',
						'LA'	=>	'CENTRAL',
						'ME'	=>	'NORTHEAST',
						'MD'	=>	'NORTHEAST',
						'MA'	=>	'NORTHEAST',
						'MI'	=>	'SOUTHEAST',
						'MN'	=>	'CENTRAL',
						'MS'	=>	'SOUTHEAST',
						'MO'	=>	'CENTRAL',
						'MT'	=>	'WEST',
						'NE'	=>	'CENTRAL',
						'NV'	=>	'WEST',
						'NH'	=>	'NORTHEAST',
						'NJ'	=>	'NORTHEAST',
						'NM'	=>	'WEST',
						'NY'	=>	'NORTHEAST',
						'NC'	=>	'SOUTHEAST',
						'ND'	=>	'CENTRAL',
						'OH'	=>	'NORTHEAST',
						'OK'	=>	'CENTRAL',
						'OR'	=>	'WEST',
						'PA'	=>	'NORTHEAST',
						'PR'	=>	'SOUTHEAST',
						'RI'	=>	'NORTHEAST',
						'SC'	=>	'SOUTHEAST',
						'SD'	=>	'CENTRAL',
						'TN'	=>	'SOUTHEAST',
						'TX'	=>	'CENTRAL',
						'UT'	=>	'WEST',
						'VT'	=>	'NORTHEAST',
						'VI'	=>	'SOUTHEAST',
						'VA'	=>	'SOUTHEAST',
						'WA'	=>	'WEST',
						'WV'	=>	'NORTHEAST',
						'WI'	=>	'CENTRAL',
						'WY'	=>	'WEST',
					// CANADA PROVINCES
						'AB'	=>	'WEST',
						'BC'	=>	'WEST',
						'MB'	=>	'WEST',
						'ON'	=>	'NORTHEAST',
						'QC'	=>	'NORTHEAST',
						'SK'	=>	'WEST',						
						);

$regionToRepMap = array(
							'WEST'		=>	array(
											'1feb9c15-391e-bf44-27f8-45cb640002a6', // Lane Edwards
											'5d5558e4-4b93-e6e3-3e89-4a283e77c993', // Gary Bazel
											),
							'NORTHEAST'	=>	array(
											'b49b4c59-794e-7f53-4846-49c7d6ccf95a', // Nate Salfen
											'a5112335-a77a-9096-d93f-4c77ea8f4764', // Christine Jarvis
											),
							'CENTRAL'	=>	array(
											'cd719c84-ea83-6d3a-6fd5-4ab411490720', // Chris Yoshida
											),
							'SOUTHEAST'	=>	array(
											'c4b76abc-556d-b445-35bf-4acfb468d0fd', // Brian Whitlock,
											),
						);

?>