// filter for input
// example return uint to input space
/*
** $(element).inputFilter(function (value) {
**      return /^\d*$/.test(value);
** });
* */

$.fn.inputFilter = function (inputFilter) {
    return this.on("input keydown keyup propertychange", function () {
        if (inputFilter(this.value)) {
            this.oldValue = this.value;
            this.oldSelectionStart = this.selectionStart;
            this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
            this.value = this.oldValue;
            this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        }
    });
};

// polyfill for closest()

(function() {
    if (!Element.prototype.closest) {
        Element.prototype.closest = function(css) {
            var node = this;
            while (node) {
                if (node.msMatchesSelector(css)) return node;
                else node = node.parentElement;
            }
            return null;
        };
    }
})();

// get GET parameters, return object, where keys = keys of GET parameters, object values = values of GET parameters

function getQueryParams(url) {
    url = url.split("+").join(" ");
    var params = {};
    var regex = /[?&]?([^=]+)=([^&]*)/g;
    var tokens;
    while (tokens = regex.exec(url)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }
    return params;
}
