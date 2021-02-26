var $mainMenu = $('mainMenu');

new mainMenuObserver();

$mainMenu.select('li a').invoke('on', 'click', function (ev) {
    var $li = ev.findElement('a').up('li');
    move_arrow_to($li);
    $mainMenu.select('li').invoke('removeClassName', 'active');
    $li.addClassName('active');
    
    if (!$li.hasClassName('extras') && $('module-dropdown').visible())
    {
        new Effect.SlideUp('module-dropdown', {duration: 0.1});
    }
});

Event.observe(window, 'load', function () {
    var $activeMenuItem = $mainMenu.down('li.active'),
        $extrasMenu     = $mainMenu.down('.extras'),
        $extrasDropdown = $('module-dropdown');
    
    if ($activeMenuItem)
    {
        move_arrow_to($activeMenuItem)
    }
    
    if ($extrasMenu && $extrasDropdown)
    {
        $extrasDropdown.setStyle({
            left: ($extrasMenu.offsetLeft + 50) + 'px',
            top:  (parseInt($('top').getHeight()) - 4) + 'px'
        });
        
        $extrasDropdown.close_all_childs = function () {
            // Hides all childs
            $$('#module-dropdown ul.moduleChilds').each(function (ele) {
                ele.hide();
                ele.previous().removeClassName('active');
            });
        };
        
        $extrasDropdown.show_childs = function (p_childID) {
            this.close_all_childs();
            
            var leftPosi = parseInt($(p_childID).previous().getWidth());
            
            // Position of the Child Tab.
            $(p_childID).setStyle({
                top:  $(p_childID).previous().offsetTop + 'px',
                left: leftPosi + 'px'
            });
            
            // Show childs.
            $(p_childID).previous().addClassName('active');
            $(p_childID).show();
        };
        
        /**
         * CloseHandler
         *
         * This handler will manage the visiblity of
         * the extrasMenu on clicking outside of it
         *
         * @param evt
         * @author Selcuk Kekec <skekec@i-doit.com>
         */
        $extrasMenu.closeHandler = function (evt) {
            /**
             *  Check whether we should skip further processing or not
             *
             *  We will skip if:
             *
             *  extraMenu link or listItem was clicked
             *  or
             *  One of the shown subMenuItems was clicked
             */
            if (
                evt.target.parentElement == $$('li.extras')[0] ||
                evt.target == $$('li.extras')[0] ||
                $(evt.target).up('#module-dropdown') !== undefined
            )
            {
                // Remove Listener
                $extrasMenu.unregisterCloseHandler();
                return;
            }
            
            // Hide extrasMenu
            new Effect.SlideUp('module-dropdown', {duration: 0.2});
            $extrasDropdown.close_all_childs();
            
            // Unregister self as listener
            if ($extrasMenu.closeHandler)
            {
                $extrasMenu.unregisterCloseHandler();
            }
        };
        
        /**
         * Register closeHandler
         *
         * @author Selcuk Kekec <skekec@i-doit.com>
         */
        $extrasMenu.registerCloseHandler = function () {
            document.body.addEventListener('click', $extrasMenu.closeHandler, true);
        };
        
        /**
         * Unregister closeHandler
         *
         * @author Selcuk Kekec <skekec@i-doit.com>^^
         */
        $extrasMenu.unregisterCloseHandler = function () {
            document.body.removeEventListener('click', $extrasMenu.closeHandler, true);
        };
        
        $extrasMenu.on('click', function (e) {
            e.preventDefault();
            
            if (!$extrasDropdown.visible())
            {
                if ($extrasDropdown.innerHTML.blank())
                {
                    new Ajax.Updater(
                        'module-dropdown',
                        '?call=modules&ajax=1',
                        {
                            method:      'POST',
                            evalScripts: true,
                            onComplete:  function () {
                                new Effect.SlideDown('module-dropdown', {duration: 0.2});
                                $extrasMenu.registerCloseHandler();
                            }
                        }
                    );
                }
                else
                {
                    new Effect.SlideDown('module-dropdown', {duration: 0.2});
                    
                    // Hides all childs.
                    $extrasDropdown.close_all_childs();
                    $extrasMenu.registerCloseHandler();
                }
            }
            else
            {
                new Effect.SlideUp('module-dropdown', {duration: 0.2});
                $extrasDropdown.close_all_childs();
                $extrasMenu.unregisterCloseHandler();
            }
        });
    }
    
    if (dragBar)
    {
        var dragBarObj = new dragBar({
            dragContainer:  'draggableBar',
            leftContainer:  'menuTreeOn',
            rightContainer: 'contentArea',
            moveInfoBox:    true,
            defaultWidth:   '[{$menu_width}]'
        });
        
        dragBarObj.callback_save = function () {
            new Ajax.Request('?call=menu&ajax=1&func=save_menu_width', {
                parameters: {
                    menu_width: $('menuTreeOn').getWidth()
                },
                method:     'post'
            });
        };
    }
});