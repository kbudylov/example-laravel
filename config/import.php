<?php
/**
 * Created by Konstantin Budylov.
 * Mailto: k.budylov@gmail.com
 * Date: 01.11.17 22:04
 **********************************************************************************/

//Import configuration
return [
	'global' => [
		'logger' => [
			'name' => 'Import',
			'logFilename' => config('app.log', __DIR__.'/../storage/logs/import.log'),
			'logLevel' => config('app.log_level', \Monolog\Logger::INFO)
		]
	],
	'vendors' => [
		'volgaline' => [
			'prefix' => 'volgaline',
			'enabled' => true,
			'model' => [
				'defaults' => [
					'ship' => [
						'showPriority' => 101
					]
				]
			],
			'client' => [
				'class' => \App\Components\Vendor\Volgaline\Client::class,
				'apiUrl' => 'http://api.volgaline.com/v1/json/ru',
				'connectionTimeout' => 0,
			],
			'logger' => [
				'name' => 'volgaline',
				'logFilename' => __DIR__.'/../storage/logs/import.volgaline.log',
				'logLevel' => \Monolog\Logger::INFO
			],
			'import' => [
				//Import jobs configuration
				'jobs' => [
					'general' => [
						'class' => \App\Jobs\Import\Volgaline\GeneralImportJob::class,
						'queue' => 'default'//'import_volgaline_general',
					],
					'ships' => [
						'class' => \App\Jobs\Import\Volgaline\ShipImportJob::class,
						'queue' => 'default'//'import_volgaline_ships'
					],
					'deleteOldShips' => [
						'class' => \App\Jobs\Import\Volgaline\DeleteOldShipsJob::class,
						'queue' => 'default'//'import_volgaline_ships'
					],
					'cruises' => [
						'class' => \App\Jobs\Import\Volgaline\CruiseImportJob::class,
						'queue' => 'default'//'import_volgaline_cruises'
					],
					'deleteOldCruises' => [
						'class' => \App\Jobs\Import\Volgaline\DeleteOldCruisesJob::class,
						'queue' => 'default'//'import_volgaline_cruises'
					],
					'syncCabinStatus' => [
						'queue' => 'sync_cabins_volgaline'
					],
					'syncPrices' => [
						//todo
					],
					'syncCruiseDates' => [
						//todo
					]
				]
			],
			'store' => [
				//todo
			]
		],
		'infoflot' => [
			'prefix' => 'infoflot',
			'enabled' => true,
			'model' => [
				'config' => [
					'shipIdActive' => [],
					'importOnlyShipIds' => [],
					'importFromShipIds' => null,
					'ignoreShipIds' => [],
					'ignoreCruiseIds' => [],
					'importOnlyCruiseIds' => []
				]
			],
			'client' => [
				'class' => \App\Components\Vendor\Infoflot\Client::class,
				'apiUrl' => 'https://api.infoflot.com/JSON/e8025dcd9ed7e2867ba5321b123eee2483006e8f',
				'connect' => [
					'defaults' => [
						'connect_timeout' => 30,
						'timeout' => 30
					]
				]
			],
			'logger' => [
				'name' => 'infoflot',
				'logFilename' => __DIR__.'/../storage/logs/import.infoflot.log',
				'logLevel' => \Monolog\Logger::INFO
			],
			'import' => [
				//Import jobs configuration
				'jobs' => [
					'general' => [
						'class' => \App\Jobs\Import\Infoflot\GeneralImportJob::class,
						'queue' => 'default'//'import_infoflot_general',
					],
					'ships' => [
						'class' => \App\Jobs\Import\Infoflot\ShipImportJob::class,
						'queue' => 'default'//'import_infoflot_ships'
					],
					'cruises' => [
						'class' => \App\Jobs\Import\Infoflot\CruiseImportJob::class,
						'queue' => 'default'//'import_infoflot_cruises'
					],
					'syncCabinStatus' => [
						'queue' => 'sync_cabins_infoflot'
					],
					'syncPrices' => [
						//todo
					],
					'syncCruiseDates' => [
						//todo
					]
				]
			],
			'store' => [
				//todo
			]
		],
        'vodohod' => [
            'prefix' => 'vodohod',
            'enabled' => true,
            'model' => [
                'config' => [
                    /*
                    'shipIdActive' => [],
                    'importOnlyShipIds' => [],
                    'importFromShipIds' => null,
                    'ignoreShipIds' => [],
                    'ignoreCruiseIds' => [],
                    'importOnlyCruiseIds' => []
                    */
                ]
            ],
            'client' => [
                'class' => \App\Components\Vendor\Vodohod\Client::class,
                'apiUrl' => 'https://www.rech-agent.ru/api/json',
                'connect' => [
                    'defaults' => [
                        'connect_timeout' => 30,
                        'timeout' => 30
                    ]
                ]
            ],
            'logger' => [
                'name' => 'vodohod',
                'logFilename' => __DIR__.'/../storage/logs/import.vodohod.log',
                'logLevel' => \Monolog\Logger::INFO
            ],
            'import' => [
                //Import jobs configuration
                'jobs' => [
                    'general' => [
                        'class' => \App\Jobs\Import\Vodohod\GeneralImportJob::class,
                        'queue' => 'default'//'import_vodohod_general',
                    ],
                    'cruises' => [
                        'class' => \App\Jobs\Import\Vodohod\CruiseImportJob::class,
                        'queue' => 'default'//'import_vodohod_cruises'
                    ],
                    'syncCabinStatus' => [
                        'queue' => 'sync_cabins_vodohod'
                    ],
                    'syncPrices' => [
                        //todo
                    ],
                    'syncCruiseDates' => [
                        //todo
                    ]
                ]
            ],
            'store' => [
                //todo
            ]
        ]
	]
];
