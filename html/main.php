<html>
    <head>
        <title><?php print $this->print_title ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        <link type="text/css" rel="stylesheet" href="<?php print $this->get_css ?>"/>
    </head>
    <body>
        <div class="top">
            <div class="container">
                <?php foreach($this->list_apps as $app => $name ) : ?>
                    <a href="<?php
                    print $this->get_host . $app;
                    ?>" target="_blank" class="right"><?php
                    print $name;
                    ?></a>
                <?php endforeach; ?>
            </div>
        </div>
            <h2 class="container header">
                <?php if( $this->has_route )  : ?>
                <a href="<?php
                    print $this->get_host ?>" target="_self"><?php
                    print $this->get_title ?></a>
                <?php foreach ( $this->list_route as $route ) : ?>
                    <span><?php print $route ?></span>
                <?php endforeach; ?>
                <?php else:  ?>
                <span><?php $this->print_title ?></span>
                <?php endif; ?>
            </h2>
        <div class="main container solid">
            <ul class="menu dashboard">
                <?php if( $this->has_folders ) : ?>
                <!-- Display hyperlinks for each folder -->
                <?php foreach ($this->list_folders as $folder) : ?>
                    <li class="item <?php
                        print strlen($folder) > $this->count_chars ? 'full-width' : 'inline' ?>">
                        <a class="content" href="<?php
                        print sprintf('%s/%s',$this->get_url,$folder) ?>"><?php
                        print $folder ?></a>
                    </li>
                <?php endforeach ; ?>
                <?php else:  ?>
                    <li class="item full-width"><h2 class="content empty"><?php
                    $this->print_empty;
                    ?></h2></li>   
                <?php endif; ?>
            </ul>
        </div>
    </body>
</html>


