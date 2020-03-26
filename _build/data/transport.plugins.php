<?php

//$modx->loadClass('modPlugin');
//$modx->loadClass('modPluginEvent');
//var_dump($modx->map['modPlugin']);
//var_dump($modx->map['modPluginEvent']);
$pconfig=[
    'events'=>[
        
    ],
    'plugins'=>[
        'NodeJsEnv'=>[
            'events'=>['OnChunkFormPrerender','OnChunkFormSave','OnLoadWebDocument']
        ],
    ]
];

foreach($pconfig['plugins']?:[] as $plugin=>$options){
    $plugin_file=$config['component']['core'].'elements/plugins/'.$plugin.'.php';
    if(!file_exists($plugin_file))continue;
    $data['modPlugin'][$plugin]=[
        'fields'=>[
            'name' => $plugin,
            'description' => $options['description']?:'',
            'plugincode' => trim(str_replace(['<?php', '?>'], '', file_get_contents($plugin_file))),
            'source' => 1,
            'property_preprocess' => 0,
            'editor_type' => 0,
            'cache_type' => 0
        ],
        'options'=>[
            'search_by'=>['name'],
        ],
        'relations'=>[
            'modCategory'=>[
                'main'=>'Plugins'
            ]
        ]
    ];
    foreach($options['events']?:[] as $event=>$fields){
        if(is_scalar($fields))$event=$fields;
        $fields=array_merge(['event'=>$event,'priority'=>0],is_array($fields)?$fields:[]);
        $data['modPluginEvent'][$plugin.'__'.$event]=[
            'fields'=>$fields,
            'options'=>[
                'search_by'=>['pluginid', 'event'],
                'preserve'=>true,
                'update'=>false
            ],
            'relations'=>[
                'modPlugin'=>[
                    $plugin=>'PluginEvents'
                ]
            ]
        ];
        if(!isset($data['modEvent'][$event])){
            
        }
    }
    unset($event,$fields);
}
foreach($pconfig['events']?:[] as $event=>$fields){
    if(is_scalar($fields))$event=$fields;
    $fields=array_merge(['name'=>$event,'service'=>6],is_array($fields)?$fields:[]);
    $data['modEvent'][$event]=[
        'fields'=>$fields,
        'options'=>[
            'search_by'=>['name'],
            'preserve'=>true
        ]
    ];
}
