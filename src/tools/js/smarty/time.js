"use strict";
function SmartyTime()
{
    var smartyTime = this;
    
    this.init = function(me, value)
    {
        Event.observe(me, 'keydown', function (evt) {
            var key = evt.key;
        
            evt.preventDefault();
            evt.stopPropagation();
        
            var pos = me.selectionStart;
        
            var c = '_';
            if (isNumeric(key)) {
                c = key;
            } else {
                switch (key) {
                    case 'Backspace':
                        c = '_';
                        pos--;
                        if (pos < 0) {
                            pos = 0;
                        }
                        if (pos == 2) {
                            pos = 1;
                        }
                        break;
                    case 'ArrowLeft':
                        me.value = value;
                        pos--;
                        if (pos == 2) {
                            pos = 1;
                        }
                        smartyTime.setCaretPosition(me, pos);
                        return null;
                        break;
                    case 'ArrowRight':
                        me.value = value;
                        pos++;
                        if (pos == 2) {
                            pos = 3;
                        }
                        smartyTime.setCaretPosition(me, pos);
                        return null;
                        break;
                    case 'Delete':
                        c = '_';
                        break;
                    default:
                        me.value = value;
                        me.simulate('change');
                        return null;
                        break;
                }
            }
        
            if (pos == 0) {
                value = smartyTime.validateTime(pos, c, (c + value.substr(1)));
            } else if (pos == 5) {
                return;
            } else {
                value = smartyTime.validateTime(pos, c, (value.substr(0,pos) + c + value.substr(pos+1)));
            }
        
            me.value = (value === '__:__') ? '' : value;
        
            if (key != 'Delete' && key != 'Backspace') {
                pos++;
            }
        
            if (pos == 2) {
                pos = 3;
            }
            smartyTime.setCaretPosition(me, pos);
            me.simulate('change');
        });
    }
    
    this.validateTime = function(pos, c, val)
    {
        switch (pos) {
            case 0:
                if (c > 2) {
                    val = '_' + val.substr(1,4);
                }
                if (+val.substr(0,2) > 23) {
                    val = '__:' + val.substr(3,2);
                }
                break;
            case 1:
                if (+val.substr(0,2) > 23) {
                    val = '__:' + val.substr(3,2);
                }
                break;
            case 3:
                if (c > 5) {
                    val = val.substr(0,2) + ':_' + val.substr(4,1);
                }
                break;
        }
        return val;
    }
    
    this.setCaretPosition = function(elem, caretPos)
    {
        if(elem != null) {
            if(elem.createTextRange) {
                var range = elem.createTextRange();
                range.move('character', caretPos);
                range.select();
            }
            else {
                if(elem.selectionStart) {
                    elem.focus();
                    elem.setSelectionRange(caretPos, caretPos);
                }
                else
                    elem.focus();
            }
        }
    }
}