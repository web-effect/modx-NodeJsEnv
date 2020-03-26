<?php

$config=[
    'project'=>dirname(__DIR__).'/',
    'build'=>__DIR__.'/',
    'resolvers' => __DIR__ . '/resolvers/',
    'includes' => __DIR__ . '/includes/',
    'data' => __DIR__ . '/data/',
    'component'=>[
        'namespace'=>'nodejsenv',
        'name'=>'NodeJsEnv',
        'version'=>'0.1',
        'release'=>'alpha',
        //'core'=>dirname(__DIR__).'/core/components/',
        //'assets'=>dirname(__DIR__).'/assets/components/',
        'resolvers'=>[
            
        ],
        'attributes'=>[
            'requires'=>['php' => '>=7.0'],
            'setup-options'=>['source' => __DIR__.'/setup.options.php']
        ]
    ],
    'modx'=>dirname(dirname(dirname(__DIR__))).'/modx/',
];

if(!$config['component']['core']){
    $config['component']['core']=$config['project'].'core/components/'.$config['component']['namespace'].'/';
}
if(!$config['component']['assets']){
    $config['component']['assets']=$config['project'].'assets/components/'.$config['component']['namespace'].'/';
}

if(!$config['component']['resolvers']['core']){
    $config['component']['resolvers']['core']=[
        'type'=>'file',
        'options'=>[
            'source' => $config['component']['core'],
            'target' => "return MODX_CORE_PATH . 'components/';",
        ]
    ];
}
if(!$config['component']['resolvers']['assets']){
    $config['component']['resolvers']['assets']=[
        'type'=>'file',
        'options'=>[
            'source' => $config['component']['assets'],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ]
    ];
}
if(!$config['component']['resolvers']['options']){
    $config['component']['resolvers']['options']=[
        'type'=>'php',
        'options'=>[
            'source' => $config['resolvers'] . 'setupoptions.resolver.php',
        ]
    ];
}

if(!$config['component']['attributes']['changelog']&&file_exists($config['component']['core'].'docs/changelog.txt')){
    $config['component']['attributes']['changelog']=file_get_contents($config['component']['core'].'docs/changelog.txt');
}
if(!$config['component']['attributes']['license']&&file_exists($config['component']['core'].'docs/license.txt')){
    $config['component']['attributes']['license']=file_get_contents($config['component']['core'].'docs/license.txt');
}
if(!$config['component']['attributes']['readme']&&file_exists($config['component']['core'].'docs/readme.txt')){
    $config['component']['attributes']['readme']=file_get_contents($config['component']['core'].'docs/readme.txt');
}


define('MODX_CORE_PATH', $config['modx']);
define('MODX_CONFIG_KEY','config');