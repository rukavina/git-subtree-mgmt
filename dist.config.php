<?php

return array(
    'tmp_dir'       => '~/tmp', //before initial push we need this tmp dir
    'subtree_dir' => __DIR__ . '/subtrees', //we need this to pull subtrees
    'project_dir' => realpath(dirname(__DIR__)),
    'module_rel_path'   => 'module', //relative path to project dir
    'git_dir' => 'ssh://user@git.example.com/modules', //path to a dir where modules are hosted
    'subtrees' => array(
        'rest'    => 'Rest' //module key => module dir
    )  
);

