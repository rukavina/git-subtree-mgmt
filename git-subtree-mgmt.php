#!/usr/local/bin/php
<?php
$shortopts = ""; // Optional value

$longopts  = array(
    "command::",
    "subtree::",
    "tag::",
    "help::",
    "package::",
    "module-dir::"
);
$options = getopt($shortopts, $longopts);

$config = require './config.php';
$subtrees = $config['subtrees'];

function checkSubtree($name){
    global $subtrees;
    
    if(!isset($subtrees[$name])){
        writeError("Subtree [$name] not defined");
        return false;
    }
    return true;
}

function writeError($error){
    echo "\n\033[31m" . $error . "\033[0m\n";    
}

function pushSubtree($name){
    global $subtrees, $config;
    
    $gitDir = $config['git_dir'];
    $moduleRelPath = $config['module_rel_path'];
    $projectDir = $config['project_dir'];
    
    if(!checkSubtree($name)){
        return false;
    }
    execCmd("git subtree push --prefix=" . $moduleRelPath . '/' . $subtrees[$name] . " " . $gitDir . ' /' . $subtrees[$name] . " master", $projectDir);
    return true;
}

function getSubtreeTmpDir($name){
    global $config;
    
    return $config['subtree_dir'] . '/' . $name;
}

function execCmd($cmd, $inPath = null){
    echo "\nexecuting: \033[35m$cmd\033[0m\n";
    if($inPath){
        echo "in dir: $inPath\n";
        $cmd = "cd $inPath && " . $cmd;
    }
    
    $output = shell_exec($cmd);  
    echo "\n\033[32m$output\033[0m\n";
}

function updateSubtree($name){
    global $subtrees;
    
    if(!checkSubtree($name)){
        return false;
    }
    
    $subtreeDir = getSubtreeTmpDir($name);
    if(!is_dir($subtreeDir)){
        execCmd("git clone " . $subtrees[$name]['url'], $subtreeDir . '/../');
    }
    else{
        execCmd("git pull " . $subtrees[$name]['url'], $subtreeDir);
    }
}

function listSubtreeTags($name){    
    if(!checkSubtree($name)){
        return false;
    }
    
    updateSubtree($name);
    $subtreeDir = getSubtreeTmpDir($name);    
    execCmd("git ls-remote --tags", $subtreeDir);
}

function tagSubtree($name, $tag){    
    if(!checkSubtree($name)){
        return false;
    }
    
    updateSubtree($name);
    $subtreeDir = getSubtreeTmpDir($name);    
    execCmd("git tag " . $tag, $subtreeDir);
    execCmd("git push --tags", $subtreeDir);
}


function action_tag($options){
    if(!isset($options['subtree'])){
        writeError('subtree not provided in param --subtree');
    }
    if(!isset($options['tag'])){
        listSubtreeTags($options['subtree']);
    } else {
        tagSubtree($options['subtree'], $options['tag']);
    }
    
}

function action_tagall($options){
    global $subtrees;
    
    if(!isset($options['tag'])){
        writeError('tag not provided in param --tag');
    }
    foreach ($subtrees as $subtree => $value) {
        tagSubtree($subtree, $options['tag']);
    }    
    
}

function action_push($options){
    global $subtrees;
    if(isset($options['subtree'])){
        pushSubtree($options['subtree']);
        return;
    }
    foreach ($subtrees as $subtree => $value) {
        pushSubtree($subtree);
    }
    
}

function action_pull($options){
    global $subtrees;
    if(!isset($options['subtree'])){
        writeError('subtree not provided in param --subtree');
        return;
    }
    foreach ($subtrees as $subtree => $value) {
        pushSubtree($subtree);
    }
    
}

function action_split($options)
{
    global $config;
    
    $gitDir = $config['git_dir'];
    $tmpFolder = $config['tmp_dir'];
    $projectFolder = $config['project_dir'];
    $moduleRelPath = $config['module_rel_path'];
    
    if(!isset($options['package'])){
        writeError('package not provided in param --package');
        exit(1);
    }    
    if(!isset($options['module-dir'])){
        writeError('module-dir not provided in param --module-dir');
        exit(1);
    }
    $package = $options['package'];
    $moduleDir = $options['module-dir'];
    
    //prepare tmp
    execCmd("rm -Rf $tmpFolder;mkdir $tmpFolder;cd $tmpFolder;git init --bare");
    
    execCmd("git subtree split --prefix=$moduleRelPath/$moduleDir -b $package", $projectFolder);
    execCmd("git push $tmpFolder/ $package:master");
    
    execCmd("git remote add origin $gitDir/$package.git", $tmpFolder);
    execCmd("git push origin master", $tmpFolder);
    
    execCmd("git rm -r $moduleRelPath/$moduleDir", $projectFolder);
    execCmd('git commit -am "Remove split code from module."', $projectFolder);
    
    execCmd("git remote add origin/modules-$package $gitDir/$package.git", $projectFolder);
    execCmd("git subtree add --prefix=$moduleRelPath/$moduleDir --squash origin/modules-$package master", $projectFolder);
    
    //git branch -D rest
    execCmd("git branch -D $package", $projectFolder);
    
    echo "
Update file [configs.php] add new key under [subtrees]:
    '$package'    => '$moduleDir',

Later, you can also use this same script to push, tag these subtrees.
";

}

function action_help($options){
    echo "

Example:
php git-subtree-mgmt.php --command=tag --subtree=contact
php git-subtree-mgmt.php --command=split --package=app --module-dir=App

Available parameters

    --command=tag|help|push|tagall|split
    --subtree=subtree module like cms, auth, teaser ...
    --package=new package name
    --module-dir module dir in modules, case sensitive
    --tag=tag/version
";
}

$command = isset($options['command']) ? $options['command']: 'help';
$actionName = 'action_' . $command;

if(!function_exists($actionName)){
    writeError("command [$command] does not exists.");
    $command = 'help';
    $actionName = 'action_' . $command;
}
$actionName($options);


