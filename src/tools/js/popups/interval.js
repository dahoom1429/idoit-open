'use strict';

/**
 * PopupInterval class for the "Interval" popup in i-doit
 *
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @version    1.0
 */

window.PopupInterval = Class.create({
    $root: null,
    
    initialize: function ($root, options) {
        this.$root = $root;
        
        this.options = {
            everyId:              'C__INTERVAL__REPEAT_EVERY',
            everyUnitId:          'C__INTERVAL__REPEAT_EVERY_UNIT',
            everyWeekOptionNames: 'C__INTERVAL__REPEAT_WEEKLY[]',
            everyWeekOptionsId:   'interval-week-options',
            everyMonthOptionsId:  'interval-month-options',
            endAfterNames:        'C__INTERVAL__END_AFTER',
            endAfterDateViewId:   'C__INTERVAL__END_DATE',
            endAfterDateHiddenId: 'C__INTERVAL__END_DATE__HIDDEN',
            endAfterEventsId:     'C__INTERVAL__END_EVENT_AMOUNT',
            config:               {}
        };
        
        Object.extend(this.options, options || {});
        
        this.$intervalEvery = $(this.options.everyId);
        this.$intervalEveryUnit = $(this.options.everyUnitId);
        this.$intervalWeekOptions = this.$root.select('[name="' + this.options.everyWeekOptionNames + '"]');
        
        this.$endAfterDateView = $(this.options.endAfterDateViewId);
        this.$endAfter = this.$root.select('[name="' + this.options.endAfterNames + '"]');
        
        // Prepare observer.
        this.prepareObserver();
        
        // Set the values, given by "this.options.config".
        this.preparePreselection();
    },
    
    prepareObserver: function () {
        this.$intervalEvery.on('updated:value', this.onIntervalEveryChange.bindAsEventListener(this));
        this.$intervalEveryUnit.on('change', this.onIntervalEveryUnitChange.bindAsEventListener(this));
    },
    
    preparePreselection: function () {
        var i;
        
        // Set values (and trigger events) for repeatEvery, repeatEveryUnit and repeatDetails:
        this.$intervalEvery.setValue(this.options.config.repeatEvery).fire('updated:value');
        this.$intervalEveryUnit.setValue(this.options.config.repeatEveryUnit).simulate('change');

        if (this.options.config.repeatEveryUnit === this.options.repeatUnit.week && Object.isArray(this.options.config.repeatDetails)) {
            for (i in this.$intervalWeekOptions) {
                if (!this.$intervalWeekOptions.hasOwnProperty(i)) {
                    continue;
                }
                
                if (this.options.config.repeatDetails.in_array(this.$intervalWeekOptions[i].readAttribute('value'))) {
                    this.$intervalWeekOptions[i].setValue(1);
                } else {
                    this.$intervalWeekOptions[i].setValue(0);
                }
            }
        } else if (this.options.config.repeatEveryUnit === this.options.repeatUnit.month) {
            if (this.options.config.repeatDetails === 'relative') {
                this.$root.down('[value="absolute"]').setValue(0);
                this.$root.down('[value="relative"]').setValue(1);
            } else {
                this.$root.down('[value="absolute"]').setValue(1);
                this.$root.down('[value="relative"]').setValue(0);
            }
        }
        
        // Switch off all radio buttons.
        this.$endAfter.invoke('setValue', 0);
        
        // Set values (and trigger events) for endAfter and endDetails
        if (this.options.config.endAfter === this.options.endAfter.date) {
            // The DatePicker will be set by PHP because the component is very tricky :(
            this.$endAfter[1].setValue(1);
        } else if (this.options.config.endAfter === this.options.endAfter.events) {
            // The event counter will be set by PHP.
            this.$endAfter[2].setValue(1);
        } else {
            this.$endAfter[0].setValue(1);
        }
    },
    
    onIntervalEveryChange: function () {
        // This will only be used to display "1 day" / "2 days" and so on.
        var intervalValue        = parseInt(this.$intervalEvery.getValue()),
            intervalUnitValueTmp = this.$intervalEveryUnit.getValue(),
            i;
        
        this.$intervalEveryUnit.update();
        
        for (i in this.options.intervalEveryUnits) {
            if (this.options.intervalEveryUnits.hasOwnProperty(i)) {
                this.$intervalEveryUnit.insert(new Element('option', {value: i}).update(this.options.intervalEveryUnits[i][(intervalValue === 1 ? 0 : 1)]));
            }
        }
        
        this.$intervalEveryUnit.setValue(intervalUnitValueTmp);
    },
    
    onIntervalEveryUnitChange: function () {
        var intervalUnitValue = this.$intervalEveryUnit.getValue();
        
        if (intervalUnitValue === this.options.repeatUnit.week) {
            $(this.options.everyWeekOptionsId).removeClassName('hide');
        } else {
            $(this.options.everyWeekOptionsId).addClassName('hide');
        }
        
        if (intervalUnitValue === this.options.repeatUnit.month) {
            $(this.options.everyMonthOptionsId).removeClassName('hide');
        } else {
            $(this.options.everyMonthOptionsId).addClassName('hide');
        }
    },
    
    getConfig: function () {
        this.options.config.repeatEvery = parseInt(this.$intervalEvery.getValue());
        this.options.config.repeatEveryUnit = this.$intervalEveryUnit.getValue();
        this.options.config.endAfter = this.$root.down('[name="C__INTERVAL__END_AFTER"]:checked').getValue();
        
        if (this.options.config.repeatEveryUnit === this.options.repeatUnit.week) {
            this.options.config.repeatDetails = this.$root.select('[name="C__INTERVAL__REPEAT_WEEKLY[]"]:checked').invoke('getValue');
        } else if (this.options.config.repeatEveryUnit === this.options.repeatUnit.month) {
            this.options.config.repeatDetails = this.$root.down('[name="C__INTERVAL__REPEAT_MONTHLY"]:checked').getValue();
        }
        
        if (this.options.config.endAfter === this.options.endAfter.date) {
            this.options.config.endDetails = $F(this.options.endAfterDateHiddenId);
        } else if (this.options.config.endAfter === this.options.endAfter.events) {
            this.options.config.endDetails = $F(this.options.endAfterEventsId);
        }
        
        return this.options.config;
    }
});