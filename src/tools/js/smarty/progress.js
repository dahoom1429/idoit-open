var progressBarInit = function (animate) {
    $$('[data-width-percent]').each(function ($el) {
        if ($el !== null) {
            var max_percent = parseFloat($el.readAttribute('data-width-percent'));
            
            if (animate) {
                $el.setStyle({width: 0, backgroundColor: Color.retrieve_color_by_percent(100)});
                
                new Effect.Morph($el, {
                    style:       'width:' + max_percent + '%',
                    duration:    2.5,
                    afterUpdate: function (effect) {
                        var calc_percent = 100 - (effect.position * max_percent);
                        
                        $el.setStyle({backgroundColor: Color.retrieve_color_by_percent(calc_percent)});
                    }
                });
            } else {
                $el.setStyle({
                    width:           max_percent + '%',
                    backgroundColor: Color.retrieve_color_by_percent(max_percent)
                });
            }
        }
    });
};