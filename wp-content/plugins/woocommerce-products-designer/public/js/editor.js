var WPD_EDITOR = (function ($, wpd_editor) {
    'use strict';
    var wpd_editor = {};
    wpd_editor.canvas = {};
    wpd_editor.serialized_parts = {};
    wpd_editor.final_canvas_parts = {};
    wpd_editor.selected_part = -1;
    wpd_editor.canvasManipulationsPosition = [];
    wpd_editor.box_center_x = false;
    wpd_editor.box_center_y = false;
    wpd_editor.scale_factor = false;
//    var output_multiplier = wpd.output_w / wpd.canvas_width;

    wpd_editor.arr_filters = ['grayscale', 'invert', 'remove-white', 'sepia', 'sepia2',
        'brightness', 'noise', 'gradient-transparency', 'pixelate',
        'blur', 'convolute'];

    $(document).ready(function () {

        $(".wpd-responsive-mode .wpc-editor-menu").toggle(
                function wpc_editor_menu_on() {

                    $(".wpd-responsive-mode .wpc-editor-col:first-child").css("display", "inline-block");
                },
                function wpc_editor_menu_off() {

                    $(".wpd-responsive-mode .wpc-editor-col:first-child").css("display", "none");

                }

        );

        $(".wpd-responsive-mode .wpc-editor-menu-right").toggle(
                function () {

                    $(".wpd-responsive-mode .wpc-editor-col.right").css("display", "inline-block");
                },
                function () {

                    $(".wpd-responsive-mode .wpc-editor-col.right").css("display", "none");

                }

        );

        var resizeId;

        var tools_accordion = new Spry.Widget.Accordion("wpc-tools-box-container", {useFixedPanelHeights: false, defaultPanel: -1});
//        var cliparts_accordion= new Spry.Widget.Accordion("img-cliparts-accordion", {useFixedPanelHeights: false, defaultPanel: -1});
//        cliparts_accordion.onPanelTabClick()
        new Spry.Widget.Accordion("my-designs-accordion", {useFixedPanelHeights: false, defaultPanel: -1});
        $("[data-tooltip-title]").otooltip();


        init_canvas();
        init_empty_canvas_data_array();

        function get_optimal_canvas_dimensions()
        {
            var available_width = $("#wpc-editor-container").outerWidth();
            var canvas_w = 0;
            var canvas_h = 0;
            if (wpd.responsive == 1 && wpd.canvas_w > available_width)
            {
                canvas_w = available_width;
                canvas_h = (canvas_w * wpd.canvas_h) / wpd.canvas_w;
            }
            else
            {
                canvas_w = wpd.canvas_w;
                canvas_h = wpd.canvas_h;
            }

            return [canvas_w, canvas_h];
        }

        $(document).on("click", "#wpc-parts-bar > li", function (e) {
            if (wpd_editor.selected_part == $(this).index())
            {
                return;
            }
            else
            {
                var part_index=$(this).index();
                load_background_overlay_if_needed(part_index);
                $("#wpc-parts-bar > li").removeClass("active");
                $(this).addClass("active");
                if (wpd_editor.selected_part >= 0)
                {
                    wpd_editor.save_canvas();
                    wpd_editor.canvas.clear();
                }
                wpd_editor.selected_part = $(this).index();
            }

            var data_id = $(this).attr("data-id");
            if (typeof wpd_editor.serialized_parts[data_id] == "undefined")//Fixe les parts non chargés lorsque le to_load est défini
            {
                wpd_editor.serialized_parts[data_id] = [];
                wpd_editor.canvasManipulationsPosition[data_id] = -1;
            }
            if (wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]])
            {
                //            resetZoom();
                $.blockUI({message: wpd.translated_strings.loading_msg});
                wpd_editor.canvas.loadFromJSON(wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]], function () {
                    load_background_overlay_if_needed(part_index);
                    rescale_canvas_if_needed();
                    $.unblockUI();
                });
            }
            wpd_editor.refresh_undo_redo_status();
        });

        function applyImageFilters() {
            wpd_editor.canvas.forEachObject(function (obj) {
                if (obj.type === 'image' && obj.filters.length) {
                    obj.applyFilters(function () {
                        obj.canvas.renderAll();
                    });
                }
            });
        }

        if (typeof to_load !== 'undefined')
            setTimeout(function () {
                preload_canvas(to_load);
            }, 500);

        function reformat_wpd_parameters_arr()
        {
            var keys = ["canvas_h", "canvas_w", "clip_h", "clip_r", "clip_rr", "clip_w", "clip_x", "clip_y", "output_loop_delay", "output_w"];
            var i = 0;
            for (i = 0; i < keys.length; i++) {
                wpd[keys[i]] = parseFloat(wpd[keys[i]]);
            }
        }

        function init_canvas()
        {
            //Converts wpd elements strings into float to avoid formatting issues
            reformat_wpd_parameters_arr();
            //We determine the best dimensions to use
            var optimal_dimensions = get_optimal_canvas_dimensions();
            wpd_editor.canvas = new fabric.Canvas('wpc-editor', {width: optimal_dimensions[0], height: optimal_dimensions[1]});
            wpd_editor.canvas.backgroundImageStretch = false;
            set_clipping_area();
            wpd_editor.canvas.renderAll();
            load_canvas_listeners();

            if (typeof wpd != 'undefined')
            {
                accounting.settings = {
                    currency: {
                        symbol: wpd.currency, // default currency symbol is '$'
                        format: wpd.price_format, // controls output: %s = symbol, %v = value/number (can be object: see below)
                        decimal: wpd.decimal_sep, // decimal point separator
                        thousand: wpd.thousand_sep, // thousands separator
                        precision: wpd.nb_decimals   // decimal places
                    },
                    number: {
                        precision: wpd.nb_decimals, // default precision on numbers is 0
                        thousand: wpd.thousand_sep,
                        decimal: wpd.decimal_sep
                    }
                }
            }
        }

        function add_rect_bounding_box(width, height, left, top, border)
        {

            var rect = new fabric.Rect({
                left: parseFloat(left),
                top: parseFloat(top),
                fill: "transparent",
                opacity: 1,
                width: width,
                height: height,
                selectable: false,
                boundingBox: true,
            });

            if (border)
            {
                rect.set("strokeWidth", 1);
                rect.set("stroke", border);
            }

            _setCustomProperties(rect);
//            console.log(rect.boundingBox);
            wpd_editor.canvas.add(rect);
            wpd_editor.canvas.renderAll();
        }

        function set_clipping_area()
        {
            //We determine the best dimensions to use
            var optimal_dimensions = get_optimal_canvas_dimensions();
            wpd_editor.canvas.clipTo = null;
            var scaleFactor = optimal_dimensions[0] / wpd.canvas_w;
            if (scaleFactor != 1)
                wpd_editor.scale_factor = scaleFactor;
            if (wpd.clip_w && wpd.clip_h && wpd.clip_w > 0 && wpd.clip_h > 0 && wpd.clip_type == "rect")
            {
                var clip_x = (optimal_dimensions[0] - wpd.clip_w * scaleFactor) / 2;
                if (wpd.clip_x || wpd.clip_x == "0")
                {
                    clip_x = wpd.clip_x * scaleFactor;
                    wpd_editor.box_center_x = parseFloat(clip_x) + parseFloat(wpd.clip_w * scaleFactor) / 2;
                    console.log(wpd_editor.box_center_x);
                }
                else if (scaleFactor != 1)
                    wpd_editor.box_center_x = wpd.canvas_w / 2;
                else
                    wpd_editor.box_center_x = optimal_dimensions[0] / 2;

                var clip_y = (optimal_dimensions[1] - wpd.clip_h * scaleFactor) / 2;
                if (wpd.clip_y || wpd.clip_y == "0")
                {
                    clip_y = wpd.clip_y * scaleFactor;
                    wpd_editor.box_center_y = parseFloat(clip_y) + parseFloat(wpd.clip_h * scaleFactor) / 2;
                }
                else if (scaleFactor != 1)
                    wpd_editor.box_center_y = wpd.canvas_h / 2;
                else
                    wpd_editor.box_center_y = optimal_dimensions[1] / 2;

                wpd_editor.canvas.clipTo = function (ctx) {
                    if (wpd.clip_type == "rect" || wpd.clip_type == "")
                    {
                        if (wpd.clip_rr > 0)
                            roundRect(ctx, (clip_x), (clip_y), parseFloat(wpd.clip_w * scaleFactor), parseFloat(wpd.clip_h * scaleFactor), parseFloat(wpd.clip_rr * scaleFactor), "", wpd.clip_border);
                        else
                        {
                            ctx.rect(clip_x, clip_y, wpd.clip_w * scaleFactor, wpd.clip_h * scaleFactor);
                            if (wpd.clip_border)
                            {
                                ctx.strokeStyle = wpd.clip_border;
                                ctx.stroke();
                            }
                        }

                    }
                };
            }
            else if (wpd.clip_r && wpd.clip_r > 0 && wpd.clip_type == "arc")
            {
                var clip_x = wpd.canvas_w / 2;
                if (wpd.clip_x)
                    clip_x = wpd.clip_x * scaleFactor;
                var clip_y = optimal_dimensions[1] - wpd.clip_h * scaleFactor / 2;
                if (wpd.clip_y)
                    clip_y = wpd.clip_y;

                wpd_editor.canvas.clipTo = function (ctx) {
                    ctx.arc(clip_x, clip_y, wpd.clip_r * scaleFactor, 0, 2 * Math.PI);

                    if (wpd.clip_border)
                    {
                        ctx.strokeStyle = wpd.clip_border;
                        ctx.stroke();
                    }
                };
            }
        }

        function load_canvas_listeners()
        {
            wpd_editor.canvas.on('object:selected', function (options) {
                $("#cb-curved").removeAttr('checked');
                if (options.target) {
                    var objectType = options.target.type;
//                    console.log(objectType);
//                    console.log(options.target.isSameColor && options.target.isSameColor() || !options.target.paths);
                    var arr_shapes = ["rect", "circle", "triangle", "polygon", "path"];
//                    console.log(objectType);
                    if (objectType == "i-text")
                    {
//                        console.log(options.target);
//                        $("#text-tools").click();
                        tools_accordion.openPanel("text-panel");
//                        var box_left=options.target.oCoords.br.x;
//                        var box_top=options.target.oCoords.br.y;
//                        $("#text-properties").css("left", box_left);
//                        $("#text-properties").css("top", box_top);
                        $('#font-family-selector').val(options.target.get("fontFamily"));//.trigger('change');;
                        $('#font-size-selector').val(options.target.get("fontSize"));
                        $('#txt-color-selector').css("background-color", options.target.get("fill"));
                        $('#txt-bg-color-selector').css("background-color", options.target.get("backgroundColor"));
                        $('#new-text').val(options.target.get("text"));
                        $(".txt-align[value='" + options.target.get("textAlign") + "']").attr('checked', 'checked');
                        $(".txt-decoration[value='" + options.target.get("textDecoration") + "']").attr('checked', 'checked');

                        var fontWeight = options.target.get("fontWeight");
                        if (fontWeight == "bold")
                            $("#bold-cb").attr('checked', 'checked');
                        else
                            $("#bold-cb").removeAttr('checked');

                        var fontStyle = options.target.get("fontStyle");
                        if (fontStyle == "italic")
                            $("#italic-cb").attr('checked', 'checked');
                        else
                            $("#italic-cb").removeAttr('checked');
                        if (options.target.get("stroke") != false && options.target.getStroke() != null)
                        {
                            $('#txt-outline-color-selector').css("background-color", options.target.get("stroke"));
                            $('#o-thickness-slider').val(options.target.get("strokeWidth"));
                        }
                        else
                        {
                            $('#o-thickness-slider').val(0);
                        }

                        var txt_opacity = options.target.opacity;
                        $("#opacity-slider").val(txt_opacity);

                    }
                    else if ((objectType == "group")&&options.target.get("originalText"))
                    {
                        //If it's a curved text, we load the first item properties (which should be the same than all other items
                        if (options.target.get("originalText"))
                        {
                            $("#cb-curved").attr('checked', 'checked');
                            tools_accordion.openPanel("text-panel");
                            $('#font-family-selector').val(options.target.item(0).get("fontFamily")).trigger('change');
                            ;
                            $('#font-size-selector').val(options.target.item(0).get("fontSize"));
                            $('#txt-color-selector').css("background-color", options.target.item(0).get("fill"));
                            $('#txt-bg-color-selector').css("background-color", options.target.item(0).get("backgroundColor"));
                            $('#new-text').val(options.target.get("originalText"));
                            $("#curved-txt-radius-slider").val(options.target.get("radius"));
                            $("#curved-txt-spacing-slider").val(options.target.get("spacing"));

                            $(".txt-align[value='" + options.target.item(0).get("textAlign") + "']").attr('checked', 'checked');
                            $(".txt-decoration[value='" + options.target.item(0).get("textDecoration") + "']").attr('checked', 'checked');

                            var fontWeight = options.target.item(0).get("fontWeight");
                            if (fontWeight == "bold")
                                $("#bold-cb").attr('checked', 'checked');
                            else
                                $("#bold-cb").removeAttr('checked');

                            var fontStyle = options.target.item(0).get("fontStyle");
                            if (fontStyle == "italic")
                                $("#italic-cb").attr('checked', 'checked');
                            else
                                $("#italic-cb").removeAttr('checked');
                            if (options.target.item(0).get("stroke") != false && options.target.item(0).getStroke() != null)
                            {
                                $('#txt-outline-color-selector').css("background-color", options.target.item(0).get("stroke"));
                                $('#o-thickness-slider').val(options.target.item(0).get("strokeWidth"));
                            }
                            else
                            {
                                $('#o-thickness-slider').val(0);
                            }

                            var txt_opacity = options.target.item(0).opacity;
                            $("#opacity-slider").val(txt_opacity);
                        }

                    }
                    else if (jQuery.inArray(objectType, arr_shapes) >= 0)
                    {
                        var shape_opacity = options.target.opacity;
                        $("#shape-opacity-slider").val(shape_opacity);
                        $('#shape-bg-color-selector').css("background-color", options.target.get("fill"));
                        $('#shape-outline-color-selector').css("background-color", options.target.get("stroke"));
                        $("#shape-thickness-slider").val(options.target.get("strokeWidth"));
                        tools_accordion.openPanel("shapes-panel");
                    }
                    else if (objectType == "image")
                    {
//                        console.log(options.target);
//                        var img_src = options.target.getSrc();
//                        console.log(img_src);
//                        var in_cliparts = $("#img-cliparts-accordion img[src='" + img_src + "']").length;
//                        var in_facebook = $("#facebook-panel img[src='" + img_src + "']").length;
//                        var in_instagram = $("#instagram-panel img[src='" + img_src + "']").length;
//
//                        if (in_cliparts)
//                            tools_accordion.openPanel("cliparts-panel");
//                        else if (in_facebook)
//                            tools_accordion.openPanel("facebook-panel");
//                        else if (in_instagram)
//                            tools_accordion.openPanel("instagram-panel");
//                        else
//                            tools_accordion.openPanel("uploads-panel");

//                        tools_accordion.openPanel("cliparts-panel");
                        open_src_panel(options);
                        var filters = options.target.filters;
                        $("#img-effects input:checkbox").removeAttr('checked');
                        $.each(filters, function (index, value) {
                            if (value)
                            {
                                var filter = value.type;
                                var matrix = value.matrix;
                                var blur_matrix = [1 / 9, 1 / 9, 1 / 9, 1 / 9, 1 / 9, 1 / 9, 1 / 9, 1 / 9, 1 / 9];
                                var sharpen_maxtrix = [0, -1, 0, -1, 5, -1, 0, -1, 0];
                                var emboss_matrix = [1, 1, 1, 1, 0.7, -1, -1, -1, -1];
                                if (filter == "Grayscale")
                                    $(".acd-grayscale").attr('checked', 'checked');
                                else if (filter == "Invert")
                                    $(".acd-invert").attr('checked', 'checked');
                                else if (filter == "Sepia")
                                    $(".acd-sepia").attr('checked', 'checked');
                                else if (filter == "Sepia2")
                                    $(".acd-sepia2").attr('checked', 'checked');
                                else if (filter == "Convolute")
                                {
                                    if (($(matrix).not(blur_matrix).length == 0 && $(blur_matrix).not(matrix).length == 0))
                                        $(".acd-blur").attr('checked', 'checked');

                                    else if (($(matrix).not(sharpen_maxtrix).length == 0 && $(sharpen_maxtrix).not(matrix).length == 0))
                                        $(".acd-sharpen").attr('checked', 'checked');

                                    else if (($(matrix).not(emboss_matrix).length == 0 && $(emboss_matrix).not(matrix).length == 0))
                                        $(".acd-emboss").attr('checked', 'checked');
                                }

                                else
                                    console.log(filter, matrix);

                            }
                        });
                        wp.hooks.doAction('WPD_EDITOR.image_selected', options.target);
                    }
                    else if ((objectType == "path" || objectType == "path-group") || (objectType == "group") && wpd.svg_colorization != "none")
                    {
//                        tools_accordion.openPanel("uploads-panel");
                        //                    $('#clipart-bg-color-selector').css("background-color",options.target.get("fill"));
                        var active_tab = open_src_panel(options, "cliparts-panel");

                        $("#" + active_tab + " .clipart-bg-color-container").html("");
                        if (options.target.isSameColor && options.target.isSameColor() || !options.target.paths) {
                            var color_picker_id = 'clipart-bg-' + 1 + '-color-selector';
                            var colorpicker_tpl = '<span id="' + color_picker_id + '" class="svg-color-selector" data-placement="top" data-tooltip-title="' + wpd.translated_strings.svg_background_tooltip + '" style="background-color:' + options.target.get("fill") + '"></span>';
                            $("#" + active_tab + " .clipart-bg-color-container").append(colorpicker_tpl);
                            $("[data-tooltip-title]").otooltip();
                            load_svg_color_picker(color_picker_id);
                        }
                        else if (options.target.paths) {
                            var used_colors = [];
                            var picker_index = 0;
                            for (var i = 0; i < options.target.paths.length; i++) {
                                var color_picker_id = 'clipart-bg-' + picker_index + '-color-selector';
                                var current_color = options.target.paths[i].fill;
                                var colorpicker_tpl = '<span id="' + color_picker_id + '" class="svg-color-selector" data-placement="top" data-tooltip-title="' + wpd.translated_strings.svg_background_tooltip + '" style="background-color:' + current_color + '" data-index="' + i + '"></span>';
                                if (wpd.svg_colorization == "by-colors")
                                {
                                    var color_pos = jQuery.inArray(current_color, used_colors);
                                    if (color_pos == -1)
                                    {
                                        $("#" + active_tab + " .clipart-bg-color-container").append(colorpicker_tpl);
                                        $("[data-tooltip-title]").otooltip();
                                        load_svg_color_picker(color_picker_id);
                                        used_colors.push(current_color);
                                        picker_index++;
                                    }
                                    else
                                    {
                                        var original_picker_id = '#clipart-bg-' + color_pos + '-color-selector';
                                        var old_indexes = $(original_picker_id).attr("data-index");
                                        $(original_picker_id).attr("data-index", old_indexes + "," + i);
                                    }
                                }
                                else
                                {
                                    $("#" + active_tab + " .clipart-bg-color-container").append(colorpicker_tpl);
                                    $("[data-tooltip-title]").otooltip();
                                    load_svg_color_picker(color_picker_id);
                                    picker_index++;
                                }
                            }
                        }

                    }

                    if (options.target.get("lockMovementX"))
                        $("#lock-mvt-x").attr('checked', 'checked');
                    else
                        $("#lock-mvt-x").removeAttr('checked');

                    if (options.target.get("lockMovementY"))
                        $("#lock-mvt-y").attr('checked', 'checked');
                    else
                        $("#lock-mvt-y").removeAttr('checked');

                    if (options.target.get("lockScalingX"))
                        $("#lock-scl-x").attr('checked', 'checked');
                    else
                        $("#lock-scl-x").removeAttr('checked');

                    if (options.target.get("lockScalingY"))
                        $("#lock-scl-y").attr('checked', 'checked');
                    else
                        $("#lock-scl-y").removeAttr('checked');

                    if (options.target.get("lockDeletion"))
                        $("#lock-Deletion").attr('checked', 'checked');
                    else
                        $("#lock-Deletion").removeAttr('checked');
                }
            });

            wpd_editor.canvas.on('object:added', function (options) {
                if (options.target) {
                    wpd_editor.canvas.calcOffset();
                    wpd_editor.canvas.renderAll();
                    options.target.setCoords();
                    var objectType = options.target.type;
                    if (objectType == "i-text")
                    {
                        reset_text_palette();
                    }
                    wpd_editor.canvas.calcOffset();
                }
            });

            wpd_editor.canvas.on('object:modified', function (options) {
                wpd_editor.canvas.calcOffset();
                wpd_editor.canvas.renderAll();
                options.target.setCoords();
                wpd_editor.save_canvas();
            });

//            if (wpd.clip_w && wpd.clip_h && wpd.clip_w > 0 && wpd.clip_h > 0 && wpd.clip_type == "rect")
//            {
//                //Bounding box beta
//                wpd_editor.canvas.on("object:moving", function (event) {
//                    //We determine the best dimensions to use
//                    var optimal_dimensions = get_optimal_canvas_dimensions();
//                    var el = event.target;
//
//                    var ratio = optimal_dimensions[0] / wpd.canvas_w;
//                    var clip_w = parseFloat(wpd.clip_w * ratio);
//                    var clip_h = parseFloat(wpd.clip_h * ratio);
//
//                    var clip_x = (optimal_dimensions[0] - wpd.clip_w * ratio) / 2;
//                    if (wpd.clip_x || wpd.clip_x == "0")
//                    {
//                        clip_x = wpd.clip_x;
//                        wpd_editor.box_center_x = parseFloat(clip_x) + parseFloat(wpd.clip_w * ratio) / 2;
//                    }
//                    var clip_y = (optimal_dimensions[1] - wpd.clip_h * ratio) / 2;
//                    if (wpd.clip_y || wpd.clip_y == "0")
//                    {
//                        clip_y = wpd.clip_y;
//                        wpd_editor.box_center_y = parseFloat(clip_y) + parseFloat(wpd.clip_h * ratio) / 2;
//                    }
//
////                var section_pos = vpc.config.sections[el.section];
//                    if (el.left + el.width * el.scaleX > clip_w + clip_x)
//                        el.left = clip_w + clip_x - el.width * el.scaleX;
//                    else if (el.left < clip_x)
//                        el.left = clip_x;
//
//                    if (el.top + el.height * el.scaleY > clip_h + clip_y)
//                        el.top = clip_h + clip_y - el.height * el.scaleY;
//                    else if (el.top < clip_y)
//                        el.top = clip_y;
//                });
//            }
        }

        function _setCustomProperties(object)
        {
            object.toObject = (function (toObject) {
                return function () {
                    return fabric.util.object.extend(toObject.call(this), {
                        lockMovementX: this.lockMovementX,
                        lockMovementY: this.lockMovementY,
                        lockScalingX: this.lockScalingX,
                        lockScalingY: this.lockScalingY,
                        lockDeletion: this.lockDeletion,
                        price: this.price,
                        originalText: this.originalText,
                        boundingBox: this.boundingBox,
                        radius: this.radius,
                        spacing: this.spacing
                    });
                };
            })(object.toObject);
        }
        wpd_editor.setCustomProperties = function (object)
        {
            _setCustomProperties(object);
        }

        wpd_editor.is_json = function (data)
        {
            if (/^[\],:{}\s]*$/.test(data.replace(/\\["\\\/bfnrtu]/g, '@').
                    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
                    replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
                return true;
            else
                return false;
        }

        wpd_editor.save_canvas = function ()
        {
            var data_id = $("#wpc-parts-bar > li:eq(" + wpd_editor.selected_part + ")").attr("data-id");
            if (typeof wpd_editor.serialized_parts[data_id] == "undefined")
                wpd_editor.serialized_parts[data_id] = ["{}"];
            //console.log(wpd_editor.serialized_parts);
            var i;
            for (i = wpd_editor.canvasManipulationsPosition[data_id]; i <= wpd_editor.serialized_parts[data_id].length - 2; i++)
            {
                wpd_editor.serialized_parts[data_id].pop();
            }

            wpd_editor.canvasManipulationsPosition[data_id]++;
            var json = JSON.stringify(wpd_editor.canvas.toJSON(['lockMovementX', 'lockMovementY', 'lockRotation', 'lockScalingX', 'lockScalingY', 'price', 'lockDeletion', 'originalText', 'radius', 'spacing']));
            wpd_editor.serialized_parts[data_id].push(json);
            update_price();
            wpd_editor.refresh_undo_redo_status();
        }

        wpd_editor.reload_background_overlay_if_needed = function (index, callback)
        {
            load_background_overlay_if_needed(index, callback);
        }

        wpd_editor.centerObjectH = function (object)
        {
            if (wpd_editor.box_center_x)
            {
                if (wpd_editor.scale_factor && wpd.clip_x)
                {
                    object.set("left", wpd_editor.box_center_x/wpd_editor.scale_factor);
                }
                else
                    object.set("left", wpd_editor.box_center_x);
            }
            else
            {
                var realWidth = object.getWidth();
                //We make sure we're making our calculations based on the scaled width
                if (wpd_editor.scale_factor)
                    realWidth = realWidth * wpd_editor.scale_factor;
                var left = (parseFloat(wpd.canvas_w) ) / 2;

                if (wpd_editor.box_center_x)
                    left = wpd_editor.box_center_x - realWidth / 2;
                //When the responsive mode is enabled, the scale factor already applies  as zoom 
                //so we remove it before setting the left
//                if (wpd_editor.scale_factor)
//                    left = left * wpd_editor.scale_factor;
//                console.log(left);

                object.set("left", left);
            }
        }

        wpd_editor.centerObjectV = function (object)
        {
            if (wpd_editor.box_center_y)
            {
                if (wpd_editor.scale_factor && wpd.clip_y)
                    object.set("top", wpd_editor.box_center_y/wpd_editor.scale_factor);
                else
                    object.set("top", wpd_editor.box_center_y);
            }
            else
            {
                var realHeight = object.getHeight();
                //We make sure we're making our calculations based on the scaled height
                if (wpd_editor.scale_factor)
                    realHeight = realHeight * wpd_editor.scale_factor;
                var top = (parseFloat(wpd.canvas_h)) / 2;
                if (wpd_editor.box_center_y)
                    top = parseFloat(wpd_editor.box_center_y) - realHeight / 2;
                //When the responsive mode is enabled, the scale factor already applies as zoom
                //so we remove it before setting the top
//                if (wpd_editor.scale_factor)
//                    top = top * wpd_editor.scale_factor;

                object.set("top", top);
            }
        }

        wpd_editor.centerObject = function (object)
        {
            wpd_editor.centerObjectV(object);
            wpd_editor.centerObjectH(object);
        }

        wpd_editor.change_item_color = function (id, hex)
        {
            $('#' + id).css('background-color', '#' + hex);
            var selected_object = wpd_editor.canvas.getActiveObject();
            if ((selected_object != null) && (selected_object.type != "group"))
            {
                wpc_set_color(id, selected_object, hex);

            }
            else if ((selected_object != null) && (selected_object.type == "group"))
            {
                selected_object.forEachObject(function (a) {
                    wpc_set_color(id, a, hex);
                });
            }
        }

        function open_src_panel(options, default_panel)
        {
            var obj = options.target.toObject();
            var img_src = obj.src;
            var in_cliparts = $("#wpd-all-cliparts img[src='" + img_src + "']").length;
            var in_facebook = $("#facebook-panel img[src='" + img_src + "']").length;
            var in_instagram = $("#instagram-panel img[src='" + img_src + "']").length;
            var in_uploads = $("#uploads-panel img[src='" + img_src + "']").length;

            if (in_cliparts)
                tools_accordion.openPanel("cliparts-panel");
            else if (in_facebook)
                tools_accordion.openPanel("facebook-panel");
            else if (in_instagram)
                tools_accordion.openPanel("instagram-panel");
            else if(!in_uploads && typeof default_panel!="undefined")
                tools_accordion.openPanel(default_panel);
            else
                tools_accordion.openPanel("uploads-panel");

            if (in_cliparts)
                return "cliparts-panel";
            else if (in_facebook)
                return "facebook-panel";
            else if (in_instagram)
                return "instagram-panel";
            else if(!in_uploads && typeof default_panel!="undefined")
                return default_panel;
            else
                return "uploads-panel";
        }


        function wpc_set_color(id, selected_object, hex)
        {
            if ((id == "txt-color-selector") || (id == "shape-bg-color-selector") || id == "clipart-bg-color-selector")
                selected_object.set("fill", '#' + hex)
            else if (id == "txt-bg-color-selector")
                selected_object.set("backgroundColor", '#' + hex);
            else if (id == "txt-outline-color-selector" || id == "shape-outline-color-selector")
                selected_object.set("stroke", '#' + hex);
            else
                console.log("unknow color selector :#" + id);

            wp.hooks.doAction('WPD_EDITOR.element_color_changed', id, selected_object, hex);

            wpd_editor.canvas.renderAll();

        }

        wpd_editor.refresh_undo_redo_status = function ()
        {
            var data_id = $("#wpc-parts-bar > li:eq(" + wpd_editor.selected_part + ")").attr("data-id");
            //console.log((wpd_editor.serialized_parts[data_id].length==1), (wpd_editor.canvasManipulationsPosition[data_id]==0));
            if ((wpd_editor.serialized_parts[data_id].length == 1) || (wpd_editor.canvasManipulationsPosition[data_id] == 0))
                $("#undo-btn").addClass("disabled");
            else
                $("#undo-btn").removeClass("disabled");

            if ((wpd_editor.serialized_parts[data_id].length > 0) && (wpd_editor.canvasManipulationsPosition[data_id] < wpd_editor.serialized_parts[data_id].length - 1))
                $("#redo-btn").removeClass("disabled");
            else
                $("#redo-btn").addClass("disabled");
        }

        function update_price()
        {
            var nb_parts = $("#wpc-parts-bar > li").length;
            var variations = {};
            var tpl = wpd.query_vars["tpl"];
            if (typeof tpl == 'undefined')
                tpl = "";

            $.each($(".wpc-qty-container"), function (key, curr_object) {
                var qty = $(this).find(".wpd-qty").val();
                variations[$(this).data("id")] = qty;
            });

            var parts_json = {};
            $.each($("#wpc-parts-bar > li"), function (key, curr_object) {
                var data_id = $(this).attr("data-id");
                if ((wpd_editor.serialized_parts[data_id]))
                {
                    var x = {};
                    x["json"] = wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]];
                    parts_json[data_id] = x;
                }

                if ($(this).index() == nb_parts - 1)
                {
                    $.post(
                            ajax_object.ajax_url,
                            {
                                action: "get_design_price",
                                variations: variations,
                                serialized_parts: JSON.stringify(parts_json),
                                tpl: tpl

                            },
                    function (data) {
                        if (wpd_editor.is_json(data))
                        {
                            var response = JSON.parse(data);
                            $.each($(".wpc-qty-container"), function (key, curr_object) {
                                var variation_id = $(this).data("id");
                                var price = response.prices[variation_id];
                                var qty = $(this).find(".wpd-qty");
                                $(this).find(".total_order").html(accounting.formatMoney(price));
                                qty.attr("uprice", price);
                                qty.trigger('change');
                            });
                        }
                        else
                            $("#debug").html(data);
                    }
                    );
                }
            });
        }

        function preload_canvas(data)
        {
            var first_part_id = $("#wpc-parts-bar > li").first().data("id");
            if (typeof data == "object")
            {
                var found_match = false;
                $.each(data, function (index, value) {
                    $.each(value, function (index1, value1) {
                        if (index1 == "json")
                        {
                            wpd_editor.serialized_parts[index] = [];
                            wpd_editor.canvasManipulationsPosition[index] = 0;
                            var json_value = value1;
                            wpd_editor.serialized_parts[index].push(json_value);
                            if (index == first_part_id)
                            {
                                found_match = true;
                                wpd_editor.selected_part = 0;
                                wpd_editor.canvas.loadFromJSON(json_value, function () {
                                    wpd_editor.canvas.renderAll.bind(wpd_editor.canvas);
                                    rescale_canvas_if_needed();
                                });
                                wpd_editor.canvas.calcOffset();
                                load_first_part_img();

                                if (json_value.indexOf('{"type":"text"') > -1) {
                                    fabric_text_to_itext();
                                }
                            }
                        }
                    });
                });
                if (!found_match)
                    $("#wpc-parts-bar > li").first().click();
                else
                    load_background_overlay_if_needed(0);//Make sure the first part data are loaded when preloading the canvas
            }
            setTimeout(function () {
                wpd_editor.canvas.renderAll();
            }, 500);

        }

        function load_first_part_img()
        {
            var bg_included = $("#wpc-parts-bar > li").first().attr("data-url");
            var bg_code = "url('" + bg_included + "') no-repeat center center";
            $("wpc-editor-container .canvas-container").css("background", bg_code);
        }

        function init_empty_canvas_data_array()
        {
            if (typeof to_load == 'undefined')
            {
                $("#wpc-parts-bar > li").each(function (key) {
                    var data_id = $(this).attr("data-id");
                    wpd_editor.serialized_parts[data_id] = [];
                    wpd_editor.canvasManipulationsPosition[data_id] = -1;
                    var nb_parts = $("#wpc-parts-bar > li").length;
                    if (key == nb_parts - 1)
                    {
                        loop_through_parts(wpd.output_loop_delay, click_on_part,
                                function () {
                                    $("#wpc-parts-bar > li").first().click();
                                    wpd_editor.canvas.renderAll();
                                    rescale_canvas_if_needed();
                                    $.unblockUI();
                                });
                    }
                });
            }
        }

        function click_on_part(part_index)
        {
            $("#wpc-parts-bar > li:eq(" + part_index + ")").click();
        }

        function loop_through_parts(delay, loop_callback, end_callback)
        {
            $.blockUI({message: wpd.translated_strings.loading_msg});
            var nb_parts = $("#wpc-parts-bar > li").length;
            var current_part = 0;
            var loopKey = setInterval(function () {
                if ($.isFunction(loop_callback))
                    loop_callback(current_part);
                if (current_part == nb_parts - 1)
                {
                    window.clearInterval(loopKey);
                    if ($.isFunction(end_callback))
                    {
                        setTimeout(function () {
                            end_callback();
                        }, delay);
                    }
                    else
                        $.unblockUI();


                }
                else
                    current_part++;
            }, delay);
        }

        function load_background_overlay_if_needed(index, callback, generating_output)
        {
            var selector = $("#wpc-parts-bar > li:eq(" + index + ")");
            var overlay_not_included = selector.attr("data-ovni");
            if (typeof generating_output == 'undefined')
                generating_output = false;
            var canvas_bg = selector.data("bg");
            if (canvas_bg == "")
                canvas_bg = null;
            var canvas_ov = selector.data("ov");
            if (canvas_ov == "")
                canvas_ov = null;

            var bg_img = new Image();
            //Both background and overlay images consider the scale when being defined so we don't need to resize them
            bg_img.onload = function () {
                var dimensions = wpd_editor.get_img_best_fit_dimensions(bg_img, wpd.canvas_w, wpd.canvas_h);
                wpd_editor.canvas.setBackgroundImage(bg_img.src, wpd_editor.canvas.renderAll.bind(wpd_editor.canvas), {
                    left: wpd.canvas_w / 2,
                    top: wpd.canvas_h / 2,
                    originX: 'center',
                    originY: 'center',
                    width: dimensions[0],
                    height: dimensions[1]
                });
            };
            if (canvas_bg != null)
                bg_img.src = canvas_bg;
            else
                wpd_editor.canvas.backgroundImage = null;

            if (overlay_not_included == "-1" && generating_output)
            {
                //White bg if CMYK mode
                if (wpd.output_format == "jpg")
                {
                    wpd_editor.canvas.setBackgroundColor("rgba(255, 255, 255, 1)", wpd_editor.canvas.renderAll.bind(wpd_editor.canvas));
                }
                wpd_editor.canvas.overlayImage = null;
                wpd_editor.canvas.renderAll.bind(wpd_editor.canvas);                
            }
            else
            {
                //White bg if CMYK mode
                if (wpd.output_format == "jpg")
                {
                    wpd_editor.canvas.setBackgroundColor("", wpd_editor.canvas.renderAll.bind(wpd_editor.canvas));
                }
                var ov_img = new Image();
                ov_img.onload = function () {
                    var dimensions = wpd_editor.get_img_best_fit_dimensions(ov_img, wpd.canvas_w, wpd.canvas_h);
                    wpd_editor.canvas.setOverlayImage(ov_img.src, wpd_editor.canvas.renderAll.bind(wpd_editor.canvas), {
                        left: wpd.canvas_w / 2,
                        top: wpd.canvas_h / 2,
                        originX: 'center',
                        originY: 'center',
                        width: dimensions[0],
                        height: dimensions[1]
                    });
                };
                if (canvas_ov != null)
                    ov_img.src = canvas_ov;
                else
                    wpd_editor.canvas.overlayImage = null;

            }
            
            //Background not included
            var bg_not_included_url = selector.attr("data-url");
            if (bg_not_included_url)
                {
                    var bg_code = "url('" + bg_not_included_url + "') no-repeat center center";
                    $("#wpc-editor-container .canvas-container").css("background", bg_code);
                }
                else
                    $("#wpc-editor-container .canvas-container").css("background", "none");

            if ($.isFunction(callback))
                setTimeout(function () {
                    callback(index);
                }, 200);
        }

        wpd_editor.get_img_best_fit_dimensions = function (img, max_width, max_height)
        {
            var w = img.width;
            var h = img.height;

            if (w < max_width && h < max_height)
                return [w, h];

            var ratio = w / h;
            w = max_width;
            h = max_width / ratio;

            if (h > max_height)
            {
                h = max_height;
                w = max_height * ratio;
            }
//            var new_w=wp.hooks.applyFilters('WPD_EDITOR.wpd_img_width', w, img);
//            console.log(new_w);
            return wp.hooks.applyFilters('WPD_EDITOR.wpd_img_dimensions', [w, h], img);
        }

        function load_svg_color_picker(id)
        {
            var selector = $('#' + id);
            var index = selector.data("index");
            var initial_color = selector.css("background-color");
            if (!initial_color)
                initial_color = "#0000ff";

            if (wpd.palette_type == "custom")
            {
                selector.qtip({
                    content: "<div class='wpc-custom-svg-colors-container' data-id='" + id + "' data-index='" + index + "'>" + wpd.palette_tpl + "</div>",
                    position: {
                        corner: {
                            target: 'middleRight',
                            tooltip: 'leftTop'
                        }
                    },
                    style: {
                        width: 200,
                        padding: 5,
                        background: 'white',
                        color: 'black',
                        //                        textAlign: 'center',
                        border: {
                            width: 1,
                            radius: 1,
                            color: '#08AED6'
                        }
                    },
                    tip: 'bottomLeft',
                    show: 'click',
                    hide: {when: {event: 'unfocus'}}
                });
            }
            else
            {
                selector.ColorPicker({
                    color: initial_color,
                    onShow: function (colpkr) {
                        $(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        $(colpkr).fadeOut(500);
                        var selected_object = wpd_editor.canvas.getActiveObject();
                        if ((selected_object != null))
                        {
                            wpd_editor.save_canvas();
                        }
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        change_svg_color(id, hex, index);
                    }
                });
            }
        }

        function change_svg_color(id, hex, index)
        {
            $('#' + id).css('background-color', '#' + hex);

            var selected_object = wpd_editor.canvas.getActiveObject();
            if ((selected_object != null) && (selected_object.type == "path" || selected_object.type == "path-group" || selected_object.type == "group"))
            {
                {
                    if (selected_object.isSameColor && selected_object.isSameColor() || !selected_object.paths) {
                        selected_object.set("fill", '#' + hex);
                    }
                    else if (selected_object.paths) {
                        if (wpd.svg_colorization == "by-colors")
                        {
                            index = $("#" + id).attr("data-index");
                            var indexes = index.split(',');
                            $.each(indexes, function (key, value)
                            {
                                selected_object.paths[value].setFill('#' + hex);
                            });
                        }
                        else
                            selected_object.paths[index].setFill('#' + hex);
                    }
                }
                wpd_editor.canvas.renderAll();

            }
        }

        $(document).on("click", ".wpc-custom-colors-container span", function (e) {
            var id = $(this).parent().data("id");
            var hex = $(this).data("color");
            wpd_editor.change_item_color(id, hex);
        });

        $(document).on("click", ".wpc-custom-svg-colors-container span", function (e) {
            var id = $(this).parent().data("id");
            var index = $(this).parent().data("index");
            var hex = $(this).data("color");
            change_svg_color(id, hex, index);
        });

        function fabric_text_to_itext() {
            //Array of property which will be used to create the i-text object
            var text_prop_array = ['active', 'angle', 'backgroundColor', 'clipTo', 'currentHeight', 'currentWidth', 'fill', 'currentWidth', 'flipX', 'flipY', 'fontFamily', 'fontSize', 'fontStyle', 'fontWeight', 'height', 'left', 'lineHeight', 'originX', 'originY', 'scaleX', 'scaleY', 'shadow', 'text', 'textAlign', 'textBackgroundColor', 'textDecoration', 'top', 'width', 'lockMovementX', 'lockMovementY', 'lockRotation', 'lockScalingX', 'lockScalingY', 'lockUniScaling'];
            setTimeout(function () {
                var canvas_objs = wpd_editor.canvas.getObjects().map(function (o) {
                    return o;
                });
                $.each(canvas_objs, function (obj_index, obj_value) {
                    if (obj_value.type == 'text') {
                        var itext = new fabric.IText("");
                        $.each(text_prop_array, function (prop_index, prop_name) {
                            itext.set(prop_name, obj_value.get(prop_name));
                        });
                        wpd_editor.canvas.remove(obj_value);
                        wpd_editor.canvas.add(itext);
                    }
                });
                wpd_editor.canvas.renderAll.bind(wpd_editor.canvas);
            }, 3600);

        }

        function reset_text_palette()
        {
            $("#new-text").val("");

            $(".txt-align").removeAttr('checked');
            $(".txt-decoration").removeAttr('checked');
            $("#bold-cb").removeAttr('checked');
            $("#italic-cb").removeAttr('checked');

            $("#font-family-selector").val($("#font-family-selector option:first").val()).trigger('change');
            ;
            $("#o-thickness-slider").val($("#o-thickness-slider option:first").val());
            $("#opacity-slider").val(1);
        }

        //Editor Actions
//        $(document).on("touchstart click", ".wpd-rp-attribute", function ()
//        {
//            var current_key=$(this).data("key");
//            var current_value=$(this).data("value");
//            //We try to find which variation is it
//            $(".wpd-rp-attribute[data-key!='"+current_key+"'").each(function (key, curr_object) {
//                
//            });
//        });

        //Preview
        $(document).on("touchstart click", "#preview-btn", function (e)
        {
            e.preventDefault();
            $("#wpd-modal .omodal-body").html("");
            //Make sure the last modification is handled
            wpd_editor.save_canvas();
            if (wpd.clip_include_in_output == "no")
                wpd_editor.canvas.clipTo = null;
            loop_through_parts(wpd.output_loop_delay,
                    generate_canvas_part,
                    function () {
                        $("#wpc-parts-bar > li").first().click();
                        $('#wpd-modal').omodal("show");
                        set_clipping_area();
                        $.unblockUI();
                    }
            );
        });

        //Download design
        $(document).on("click", "#download-btn", function ()
        {
            $("#debug").html("");
            if (wpd.clip_include_in_output == "no")
                wpd_editor.canvas.clipTo = null;
            loop_through_parts(wpd.output_loop_delay,
                    generate_final_canvas_part,
                    function () {
                        if (jQuery.isEmptyObject(wpd_editor.final_canvas_parts))
                        {
                            $("#debug").html("<div class='wpc-failure'>" + wpd.translated_strings.empty_object_msg + "</div>");
                            set_clipping_area();
                            $.unblockUI();
                        }
                        else
                        {
                            var variation_id = wpd.global_variation_id;
                            var frm_data = new FormData();
                            frm_data.append("action", "generate_downloadable_file");
                            frm_data.append("variation_id", variation_id);
                            frm_data.append("format", wpd.output_format);
                            frm_data = convert_final_canvas_parts_to_blob(frm_data);

                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                data: frm_data,
                                processData: false,
                                contentType: false
                            }).done(function (data) {
                                set_clipping_area();
                                $.unblockUI();
                                if (wpd_editor.is_json(data))
                                {
                                    var response = JSON.parse(data);
                                    if ($("#wpc-parts-bar > li").length > 1)
                                    {
                                        $("#wpc-parts-bar > li").first().click();
                                    }
                                    else
                                        reload_first_part_data();

                                    $("#debug").html(response.message);
                                    wp.hooks.doAction('WPD_EDITOR.download_files_complete');
                                }
                                else
                                    $("#debug").html(data);
                            });
                        }
                    }
            );
        });

        //Save design for later
        $(document).on("touchstart click", "#save-btn", function ()
        {
            loop_through_parts(wpd.output_loop_delay,
                    generate_final_canvas_part,
                    function () {
                        if (jQuery.isEmptyObject(wpd_editor.final_canvas_parts))
                        {
                            $("#debug").html("<div class='wpc-failure'>" + wpd.translated_strings.empty_object_msg + "</div>");
                            $.unblockUI();
                        }
                        else
                        {
                            var quantity = $("#wpd-qty").val();
                            var index = $("#save-btn").data("index");
                            var variation_id = wpd.global_variation_id;
                            var frm_data = new FormData();
                            frm_data.append("action", "save_custom_design_for_later");
                            frm_data.append("variation_id", variation_id);
                            frm_data.append("design_index", index);
                            frm_data.append("format", wpd.output_format);
                            frm_data = convert_final_canvas_parts_to_blob(frm_data);
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                data: frm_data,
                                processData: false,
                                contentType: false
                            }).done(function (data) {
                                $.unblockUI();
                                if (wpd_editor.is_json(data))
                                {
                                    var response = JSON.parse(data);
                                    $("#wpc-parts-bar > li").first().click();
                                    if (!data.is_logged)
                                        $(location).attr('href', response.url);
                                    else
                                    {
                                        if (data.success)
                                            $(location).attr('href', response.url);
                                    }
                                }
                                else
                                    $("#debug").html(data);
                            });
                        }
                    }
            );
        });

        //Quantity setter
        $(document).on('click', '.wpc-qty-container .plus, .wpc-qty-container .minus', function () {

            // Get values
            var $qty = $(this).siblings(".wpd-qty");//$("#wpd-qty"),
            var currentVal = parseFloat($qty.val());
            var max = parseFloat($qty.attr('max'));
            var min = parseFloat($qty.attr('min'));
            var step = $qty.attr('step');

            // Format values
            if (!currentVal || currentVal === '' || currentVal === 'NaN')
                currentVal = 0;
            if (max === '' || max === 'NaN')
                max = '';
            if (min === '' || min === 'NaN')
                min = 0;
            if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN')
                step = 1;

            // Change the value
            if ($(this).is('.plus')) {

                if (max && (max == currentVal || currentVal > max)) {
                    $qty.val(max);
                } else {
                    $qty.val(currentVal + parseFloat(step));
                }

            } else {

                if (min && (min == currentVal || currentVal < min)) {
                    $qty.val(min);
                } else if (currentVal > 0) {
                    $qty.val(currentVal - parseFloat(step));
                }

            }

            // Trigger change event
            $qty.trigger('change');

            //For WAD (bulk discounts)
            update_price();
        });

        $('.wpd-rp-attribute.cart-item-edit').click(function () {
            return confirm(wpd.translated_strings.cart_item_edition_switch);
        });

        //We remove the existing cookie if needed
//        $.removeCookie('wpd-data-to-load');

        $(document).on("touchstart click", ".wpd-rp-attribute", function (e) {
            e.preventDefault();
            $.blockUI({message: wpd.translated_strings.loading_msg});
            var serialized_parts = {};
            var href = $(this).attr("href");
            $.each(wpd_editor.serialized_parts, function (key, curr_object) {
                serialized_parts[key] = curr_object[curr_object.length - 1];
//                console.log(curr_object);
            });

            var json_parts = JSON.stringify(serialized_parts);
//            console.log(json_parts);

            $.post(
                    ajax_object.ajax_url,
                    {
                        action: "save_data_to_reload",
                        serialized_parts: json_parts
                    },
            function (data) {
                if (wpd_editor.is_json(data))
                {
                    $.unblockUI();
//                            var response = JSON.parse(data);
                    $(location).attr('href', href);
                }
                else
                    $("#debug").html(data);
            }
            );
            //var cookie_value=JSON.stringify(wpd_editor.serialized_parts);
//            $.cookie("wpd-data-to-load", cookie_value);
//            $(location).attr('href', $(this).attr("href"));
//            loop_through_parts(wpd.output_loop_delay,
//                    generate_final_canvas_part,
//                    function () {
//                        if (jQuery.isEmptyObject(wpd_editor.final_canvas_parts))
//                        {
//                            $("#debug").html("<div class='wpc-failure'>" + wpd.translated_strings.empty_object_msg + "</div>");
//                            $.unblockUI();
//                        }
//                        else
//                        {
//                            frm_data.append("final_canvas_parts", wpd_editor.final_canvas_parts);
//                        }
//                    });
        });

        $(document).on("change", '.wpd-qty', function ()
        {
            var qty = $(this).val();
            var unit_price = $(this).attr("uprice");
            var opt_price = $(this).attr("opt_price");
            var total_field = $(this).siblings(".total-price").find(".total_order");
            if (!$.isNumeric(qty))
            {
                $(this).val(1);
                total_field.html(accounting.formatMoney(unit_price));
                return;
            }
            if ($.isNumeric(opt_price)) {
                unit_price = parseFloat(unit_price) + parseFloat(opt_price);
                unit_price = wp.hooks.applyFilters('wpd.unit_price', unit_price, $(this));
            }
            var total = unit_price * qty;
            //total_field.html($.number(total, wpd.nb_decimals, wpd.decimal_sep, wpd.thousand_sep));
            total_field.html(accounting.formatMoney(total));
            //$("#total_order").html(total.toFixed(2));

        });

        //ninja form validation
        var wpd_ninja_form_validation = function () {
            var deferred = new $.Deferred();
            if ($('.wpc-container .ninja-forms-form').length > 0) {


                $(".wpc-container form.ninja-forms-form").each(function () {
                    var form = $(this);
                    if (form.find('input[type="submit"]').length < 1) {
                        form.append('<input type="submit" class="ninja-forms-field" style="display:none;"/>');
                    }
                    var form_id = form.attr('id');
                    $('#' + form_id).submit();
                    $(document).on('submitResponse.example', function (e, response) {
                        //console.log(response);
                        form.show();
                        if (response.success) {
                            deferred.resolve(true);
                        }
                        else {
                            if ($("#wpc-parts-bar > li").length > 1)
                            {
                                $("#wpc-parts-bar > li").first().click();
                            }
                            else
                                reload_first_part_data();

                            //$("#debug").html("<div class='wpc-failure'>" + wpd.translated_strings.empty_object_msg + "</div>");
                            // responses.push(false);
                            //console.log('false');
                            deferred.resolve(false);
                            //$.unblockUI();
                            // return false;
                        }
                    });
                });


            } else {
                deferred.resolve(true);
            }
            return deferred.promise();
        }

        //Add to cart
        $(document).on("touchstart click", "#add-to-cart-btn", function ()
        {
            $("#debug").html("");
            var variations = {};

            $.each($(".wpc-qty-container"), function (key, curr_object) {
                var qty = $(this).find(".wpd-qty").val();
                variations[$(this).data("id")] = qty;
            });

            //Make sure the last modification is handled
            wpd_editor.save_canvas();

            wpd_ninja_form_validation().then(function function_name(form_is_valid) {
                // body...

                if (!form_is_valid) {
                    //console.log('! form_is_valid');
                    $.unblockUI();
                    // return false;
                } else {
//            console.log(wpd.output_loop_delay);
                    if (wpd.clip_include_in_output == "no")
                        wpd_editor.canvas.clipTo = null;
                    loop_through_parts(wpd.output_loop_delay,
                            generate_final_canvas_part,
                            function () {
                                //console.log("End");
                                //console.log(wpd_editor.final_canvas_parts);
                                if (jQuery.isEmptyObject(wpd_editor.final_canvas_parts))
                                {
                                    $("#debug").html("<div class='wpc-failure'>" + wpd.translated_strings.empty_object_msg + "</div>");
                                    set_clipping_area();
                                    $.unblockUI();
                                }
                                else
                                {
                                    //console.log(variations.toString());
                                    var quantity = $("#wpd-qty").val();
                                    var variation_id = wpd.global_variation_id;
                                    //var cart_item_key = GetURLParameter("edit");
                                    var cart_item_key = wpd.query_vars["edit"];
                                    if (typeof cart_item_key == 'undefined')
                                        cart_item_key = "";
                                    var tpl = wpd.query_vars["tpl"];
                                    if (typeof tpl == 'undefined')
                                        tpl = "";
                                    var frm_data = new FormData();
                                    frm_data.append("variation_id", variation_id);
                                    frm_data.append("variations", JSON.stringify(variations));
                                    frm_data.append("format", wpd.output_format);
                                    frm_data.append("action", "add_custom_design_to_cart");
                                    frm_data.append("cart_item_key", cart_item_key);
                                    frm_data.append("tpl", tpl);
                                    frm_data.append("final_canvas_parts", wpd_editor.final_canvas_parts);
                                    frm_data.append("quantity", quantity);
                                    var wpd_design_options = JSON.stringify(get_design_options());
                                    frm_data.append("wpd-design-opt", wpd_design_options);
                                    frm_data = convert_final_canvas_parts_to_blob(frm_data);

                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        data: frm_data,
                                        processData: false,
                                        contentType: false
                                    }).done(function (data) {
                                        if (wpd_editor.is_json(data))
                                        {
                                            var response = JSON.parse(data);
                                            if ($("#wpc-parts-bar > li").length > 1)
                                                $("#wpc-parts-bar > li").first().click();
                                            else
                                                reload_first_part_data();
                                            if (wpd.redirect_after == 1 && response.success)
                                            {
                                                $(location).attr('href', response.url);
                                            }
                                            else
                                            {
                                                $("#debug").html(response.message);
                                                set_clipping_area();
                                                $.ajax( $fragment_refresh );
                                                $.unblockUI();
                                            }
                                        }
                                        else
                                        {
                                            $("#debug").html(data);
                                            set_clipping_area();
                                            $.ajax( $fragment_refresh );
                                            $.unblockUI();
                                        }

                                    });
                                }
                            }
                    );
                }
            });
        });
        
        var $fragment_refresh = {
        url: wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'get_refreshed_fragments' ),
        type: 'POST',
        success: function( data ) {
            if ( data && data.fragments ) {

                $.each( data.fragments, function( key, value ) {
                    $( key ).replaceWith( value );
                });

                $( document.body ).trigger( 'wc_fragments_refreshed' );
            }
        }
    };
        /*function GetURLParameter(sParam)
         {
         var sPageURL = window.location.search.substring(1);
         var sURLVariables = sPageURL.split('&');
         for (var i = 0; i < sURLVariables.length; i++)
         {
         var sParameterName = sURLVariables[i].split('=');
         if (sParameterName[0] == sParam)
         {
         return sParameterName[1];
         }
         }
         
         console.log(wpd.query_vars[sParam]);
         if (wpd.query_vars.sParam != undefined) {
         return wpb.query_vars.sParam;
         };
         }*/



        $("#lock-mvt-x, #lock-mvt-y, #lock-scl-x, #lock-scl-y, #lock-Deletion").change(function (e)
        {
            var property = $(this).data("property");
            var selected_object = wpd_editor.canvas.getActiveObject();
            var selected_group = wpd_editor.canvas.getActiveGroup();
            if (selected_object != null)
            {
                if ($(this).is(':checked'))
                    selected_object[property] = true;
                else
                    selected_object[property] = false;
                wpd_editor.save_canvas();
            }
            else if (selected_group != null)
            {
                if ($(this).is(':checked'))
                    selected_group[property] = true;
                else
                    selected_group[property] = false;
                wpd_editor.save_canvas();
            }
        });

        $('.post-type-wpc-template #publish').click(function (e)
        {
            e.preventDefault();
            loop_through_parts(wpd.output_loop_delay,
                    generate_final_canvas_part,
                    function () {
                        if (jQuery.isEmptyObject(wpd_editor.final_canvas_parts))
                        {
                            alert(wpd.translated_strings.empty_object_msg);
                            $.unblockUI();
                        }
                        else
                        {
                            var frm_data = new FormData();
                            frm_data.append("action", "save_canvas_to_session");
                            frm_data = convert_final_canvas_parts_to_blob(frm_data);

                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                data: frm_data,
                                processData: false,
                                contentType: false
                            }).done(function (data) {
//                                console.log(data);
//                                $("#wpc-parts-bar > span").first().click();
                                $("#post").submit();

                            });
                        }

                    }
            );
        });

        //Generate design for output
        function generate_final_canvas_part(part_index)
        {
            //        resetZoom(true); 
            generate_canvas_part(part_index, false);
        }

        function generate_canvas_part(part_index, preview)
        {
            wpd_editor.selected_part = part_index;
            preview = typeof preview !== 'undefined' ? preview : true;
            var data_id = $("#wpc-parts-bar > li:eq(" + part_index + ")").attr("data-id");
            var data_part_img = $("#wpc-parts-bar > li:eq(" + part_index + ")").attr("data-url");
            wpd_editor.canvas.clear();
            if (typeof wpd_editor.serialized_parts[data_id] == "undefined")
            {
                wpd_editor.serialized_parts[data_id] = ["{}"];
            }
//            console.log(wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]]);
            wpd_editor.canvas.loadFromJSON(wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]],
                    function () {
                        applyImageFilters();
                        load_background_overlay_if_needed(wpd_editor.selected_part, function () {

                            var multiplier = wpd.output_w / wpd_editor.canvas.getWidth();
                            if (preview)
                                multiplier = 1;
                            //We split the multiplier per 2 if we're in retina mode
                             if( window.devicePixelRatio !== 1 )
                                 multiplier=multiplier/2;
                            var image = wpd_editor.canvas.toDataURL({format: wpd.output_format, multiplier: multiplier, quality: 1});
                            var svg = "";
                            if (wpd.generate_svg)
                                svg = wpd_editor.canvas.toSVG();

                            var blob_image = dataURItoBlob(image);

                            if (preview)
                            {
                                var modal_content = "";
                                if (wpd.watermark)
                                {
                                    var frm_data = new FormData();
                                    frm_data.append("action", "get_watermarked_preview");
                                    frm_data.append("watermark", wpd.watermark);
                                    frm_data.append("product-id", wpd.global_variation_id);
                                    frm_data.append("image", blob_image);

//                                    frm_data = convert_final_canvas_parts_to_blob(frm_data);

                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        data: frm_data,
                                        processData: false,
                                        contentType: false
                                    }).done(function (data) {
                                        if (wpd_editor.is_json(data))
                                        {
                                            var response = JSON.parse(data);
                                            if (data_part_img)
                                                modal_content = "<div style='background-image:url(" + data_part_img + ");'><img src='" + response.url + "'></div>";
                                            else
                                                modal_content = "<div><img src='" + response.url + "'></div>";
                                            $("#wpd-modal .omodal-body").append(modal_content);
                                        }
                                        else
                                        {
                                            $("#debug").html(data);
                                        }

                                    });
                                }
                                else
                                {
                                    if (data_part_img)
                                        modal_content = "<div style='background-image:url(" + data_part_img + ");'><img src='" + image + "'></div>";
                                    else
                                        modal_content = "<div><img src='" + image + "'></div>";
                                    $("#wpd-modal .omodal-body").append(modal_content);
                                }
                            }
                            else
                            {
                                var canvas_obj = $.parseJSON(wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]]);
                                var layers = [];
                                if (wpd.print_layers)
                                {
                                    var objects = canvas_obj.objects;
                                    $.each(objects, function (key, curr_object) {
                                        wpd_editor.canvas.clear();
                                        var tmp_canvas_obj = canvas_obj;
                                        tmp_canvas_obj.objects = [curr_object];
                                        var tmp_canvas_json = JSON.stringify(tmp_canvas_obj);
                                        wpd_editor.canvas.loadFromJSON(tmp_canvas_json, function () {
                                            applyImageFilters();
                                            wpd_editor.canvas.renderAll.bind(wpd_editor.canvas);
                                            //Removes overlay not included from layers
                                            load_background_overlay_if_needed(wpd_editor.selected_part, "", true);
                                            var multiplier = wpd.output_w / wpd_editor.canvas.getWidth();
                                            var layer = wpd_editor.canvas.toDataURL({format: wpd.output_format, multiplier: multiplier, quality: 1});
                                            //console.log(layer);
                                            var blob_layer = dataURItoBlob(layer);
                                            layers.push(blob_layer);
                                            //Loads the complete canvas before the save later otherwise, we end up with the last layer loaded as part data
                                            if (key == objects.length - 1)
                                            {
                                                wpd_editor.canvas.loadFromJSON(wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]]);
                                                applyImageFilters();
                                            }
                                        });
                                    });
                                }
                                wpd_editor.final_canvas_parts[data_id] = {json: wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]], image: blob_image, original_part_img: data_part_img, layers: layers, svg: svg};
                            }
                            load_background_overlay_if_needed(wpd_editor.selected_part);
                        }, true);
                    });
        }

        function dataURItoBlob(dataURI) {
            // convert base64/URLEncoded data component to raw binary data held in a string
            var byteString;
            if (dataURI.split(',')[0].indexOf('base64') >= 0)
                byteString = atob(dataURI.split(',')[1]);
            else
                byteString = unescape(dataURI.split(',')[1]);

            // separate out the mime component
            var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

            // write the bytes of the string to a typed array
            var ia = new Uint8Array(byteString.length);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }

            var blob = new Blob([ia], {type: mimeString});
//            console.log(blob.size);

            return blob;
        }

        function convert_final_canvas_parts_to_blob(frm_data)
        {
            $.each(wpd_editor.final_canvas_parts, function (part_key, part_data) {
                $.each(part_data, function (data_key, data_value) {
                    if (data_key == "image")
                        frm_data.append(part_key + "[" + data_key + "]", data_value);
                    else if (data_key == "layers")
                    {
                        $.each(data_value, function (layer_index, layer_data) {
                            frm_data.append("layers[" + part_key + "][]", layer_data);
                        });
                    }
                    else
                        frm_data.append("final_canvas_parts[" + part_key + "][" + data_key + "]", data_value);
                });
            });
            return frm_data;
        }

        function reload_first_part_data()
        {
            var data_id = $("#wpc-parts-bar > li:eq(0)").attr("data-id");
            wpd_editor.canvas.clear();
            //console.log(wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]])
            wpd_editor.canvas.loadFromJSON(wpd_editor.serialized_parts[data_id][wpd_editor.canvasManipulationsPosition[data_id]], function () {
                wpd_editor.canvas.renderAll.bind(wpd_editor.canvas);
                rescale_canvas_if_needed();
            });
        }

        function rescale_canvas_if_needed()
        {
            if (wpd.responsive != 1)
                return false;
            var optimal_dimensions = get_optimal_canvas_dimensions();
            var scaleFactor = optimal_dimensions[0] / wpd.canvas_w;
//            var current_canvas_w=wpd_editor.canvas.getWidth();
//            
//            console.log(current_canvas_w+"=>"+optimal_dimensions[0]);
            if (scaleFactor != 1) {
//                console.log("Factor: "+scaleFactor);
                wpd_editor.scale_factor = scaleFactor;
                wpd_editor.canvas.setWidth(optimal_dimensions[0]);
                wpd_editor.canvas.setHeight(optimal_dimensions[1]);
//                var xpos=optimal_dimensions[0]/2;
//                var ypos=optimal_dimensions[1]/2;
                wpd_editor.canvas.setZoom(scaleFactor);
                wpd_editor.canvas.calcOffset();
                wpd_editor.canvas.renderAll();
            }

            applyImageFilters();
        }

        $(window).resize(function () {
            clearTimeout(resizeId);
            resizeId = setTimeout(handle_resize, 500);
        });

        function handle_resize()
        {
            $(".canvas-container").hide();
            rescale_canvas_if_needed();
            set_clipping_area();
            $(".canvas-container").show();

        }

//        Shortcuts
        if(parseInt(wpd.disable_shortcuts)!=1)
        {
            $(document).keydown(function (e) {
                var selected_object = wpd_editor.canvas.getActiveObject();
                var selected_group = wpd_editor.canvas.getActiveGroup();

                if (e.which == 46) //Delete button
                    $("#delete_btn").click();
                else if (e.which == 37) //Left button
                {
                    if (selected_group != null && !selected_group.get("lockMovementX"))
                    {
                        selected_group.set("left", selected_group.left - 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }
                    else if ((selected_object != null) && !selected_object.get("lockMovementX"))
                    {
                        selected_object.set("left", selected_object.left - 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }

                }
                else if (e.which == 39) //Right button
                {
                    if (selected_group != null && !selected_group.get("lockMovementX"))
                    {
                        selected_group.set("left", selected_group.left + 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }
                    else if ((selected_object != null) && !selected_object.get("lockMovementX"))
                    {
                        selected_object.set("left", selected_object.left + 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }
                }
                else if (e.which == 38) //Top button
                {

                    if (selected_group != null && !selected_group.get("lockMovementY"))
                    {
                        e.preventDefault();
                        selected_group.set("top", selected_group.top - 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }
                    else if ((selected_object != null) && !selected_object.get("lockMovementY"))
                    {
                        e.preventDefault();
                        selected_object.set("top", selected_object.top - 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }
                }
                else if (e.which == 40) //Bottom button
                {
                    if (selected_group != null && !selected_group.get("lockMovementY"))
                    {
                        e.preventDefault();
                        selected_group.set("top", selected_group.top + 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }
                    else if ((selected_object != null) && !selected_object.get("lockMovementY"))
                    {
                        e.preventDefault();
                        selected_object.set("top", selected_object.top + 1);
                        wpd_editor.canvas.renderAll();
                        wpd_editor.save_canvas();
                    }

                }
                else if (e.keyCode == 67 && e.ctrlKey)//ctrl+c
                {
                    $("#copy_paste_btn").click();
                }
    //        else if(e.keyCode == 86 && e.ctrlKey)//ctrl+v
    //        {
    //            $("#copy_paste_btn").click();
    //        }
                else if (e.keyCode == 90 && e.ctrlKey)//ctrl+z
                {
                    $("#undo-btn").click();
                }
                else if (e.keyCode == 89 && e.ctrlKey)//ctrl+y
                {
                    $("#redo-btn").click();
                }
            });
        }

        $(".wpd-rp-attribute").mouseenter(function () {
            $("#wpd-rp-desc").html($(this).data("desc"));
        });

        $(".wpd-rp-attribute").mouseout(function () {
            var default_desc = $(".wpd-rp-attribute.selected").data("desc");
            $("#wpd-rp-desc").html(default_desc);
        });

        $('canvas').bind('contextmenu', function (e) {
            return false;
        });

        /**
         * Draws a rounded rectangle using the current state of the canvas.
         */
        function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
            if (typeof stroke == "undefined") {
                stroke = false;
            }
            if (typeof radius === "undefined") {
                radius = 5;
            }
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
            if (stroke) {
                ctx.strokeStyle = stroke;
                ctx.stroke();
            }
            if (fill) {
                ctx.fill();
            }
        }
    });



    return wpd_editor;
}(jQuery, WPD_EDITOR));