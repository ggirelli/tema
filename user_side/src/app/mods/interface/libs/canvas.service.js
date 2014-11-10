(function () {
    "use strict";

    define([], function () {

        return function (q, http) {
            var self = this;

            self.elements = {
                nodes: [
                    { data: { id: "n1", name: "Welcome" } },
                    { data: { id: "n2", name: "to" } },
                    { data: { id: "n3", name: "SOGI" } }
                ],

                edges: [
                    { data: { id: "e1", source: "n1", target: "n2" } },
                    { data: { id: "e2", source: "n2", target: "n3" } },
                    { data: { id: "e3", source: "n3", target: "n2" } }
                ]
            };

            self.init = function () {
                // Initialize canvas
                window.cy = cytoscape({
                    container: document.getElementById('canvas'),
                    layout: {
                        name: 'circle',
                        fit: true,
                        padding: 5
                    },
                    style: cytoscape.stylesheet()
                        .selector('node').css({
                            'background-color': 'white',
                            'border-color': '#909090',
                            'border-width': '1px',
                            'content': 'data(name)',
                            'text-valign': 'center',
                            'color': '#323232',
                            'min-zoomed-font-size': '10px',
                            'font-family': 'arial',
                            'text-outline-color': 'white',
                            'text-outline-width': '1'
                        })
                        .selector('edge').css({
                            'target-arrow-shape': 'triangle'
                        })
                        .selector(':selected').css({
                            'background-color': 'black',
                            'line-color': 'black',
                            'target-arrow-color': 'black',
                            'source-arrow-color': 'black'
                        })
                        .selector('.faded').css({
                            'opacity': 0.25,
                            'text-opacity': 0
                        }),

                    ready: function () {
                        var cy = this;

                        cy.load(self.elements, undefined, function () {
                            cy.layout({name:"random"});
                        });
                    }

                });
            };

        };

    });

}());
