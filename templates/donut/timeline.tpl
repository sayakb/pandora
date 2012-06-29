/*****************************************
 DONUT - Timeline visualizer
 Open source under the BSD License.
 Copyright (c) 2009 Michael Aufreiter
 Copyright (c) 2012 KDE WebTeam
 All rights reserved.
*****************************************/

color[] colors = new color[12];

colors[0] = color(171, 199, 49);
colors[1] = color(162, 195, 85);
colors[2] = color(154, 191, 123);
colors[3] = color(147, 186, 161);
colors[4] = color(141, 181, 200);
colors[5] = color(134, 162, 169);
colors[6] = color(128, 142, 137);
colors[7] = color(122, 122, 104);
colors[8] = color(177, 102, 73);
colors[9] = color(131, 127, 67);
colors[10] = color(157, 175, 55);

color LABEL_COLOR = color(150);
color HIGHLIGHTED_LABEL_COLOR = color(55);
color DONUT_BACKGROUND = color(255);
color BACKGROUND = color(244);

// Global constants
float STROKE_WEIGHT_COLLAPSED = 50;
float STROKE_WEIGHT_EXPANDED = 60;
float OUTER_BORDER = 5;
float EPSILON = TWO_PI / 100; // Tolerance for applying angleAdjustment

// Performance fixes
var hoverTimeout;

public class DonutSlice {
    String id = "";
    String name = "";
    boolean hovering = false;
    DonutSegment seg;
    float angleStart;
    float angleStop;
    
    public DonutSlice(String id, String name, DonutSegment seg) {
        this.id = id;
        this.name = name;
        this.seg = seg;
    }
    
    public void update() {
        // Nothing goes here
    }
    
    public void draw() {
        float radius = seg.donut.radius;
        stroke(255, 255, 255, 100);
        strokeWeight(1);
    
        line(this.seg.donut.x + cos(this.angleStop) * (radius - seg.breadth / 2),
             this.seg.donut.y + sin(this.angleStop) * (radius - seg.breadth / 2),
             this.seg.donut.x + cos(this.angleStop) * (radius + seg.breadth / 2),
             this.seg.donut.y + sin(this.angleStop) * (radius + seg.breadth / 2));
    
        // Highlight selectedSlice
        if (this.hovering) {
            curContext.lineCap = "butt";
            noFill();
            stroke(0,0,0,20);
            strokeWeight(seg.breadth);
            arc(seg.donut.x, seg.donut.y, seg.donut.radius * 2, seg.donut.radius * 2, angleStart, angleStop);
        }
    
        noStroke();
        strokeWeight(0); // Reset
    
        if (seg.donut.selectedSegment === this.seg && !seg.fan.isPlaying()) {
            float theta = ((this.angleStart + this.angleStop) / 2) % TWO_PI + 0.05;
            float angle = theta;
    
            curContext.font = "14px Open Sans";
            curContext.textAlign = "left";
    
            if (theta > PI/2 && theta < (PI + PI / 2) - 0.05) {
                fWidth = curContext.measureText(this.name).width;
                angle -= 0.05;
            } else {
                fWidth = 0;
            }
    
            pushMatrix();
            translate(seg.donut.x, seg.donut.y);
            rotate(angle);
            pushMatrix();
            translate(seg.donut.radius + 50 + fWidth,0);
            
            if (this.hovering)
                fill(HIGHLIGHTED_LABEL_COLOR);
            else
                fill(LABEL_COLOR);

            pushMatrix();
            
            if (theta > PI / 2 && theta < (PI + PI / 2) - 0.05) {
                rotate(PI);
            }

            popMatrix();
            popMatrix();
            popMatrix();
        }
    }
    
    public boolean checkSelected() {
        float disX = mouseX - seg.donut.x;
        float disY = mouseY - seg.donut.y;
    
        // Calculate polar coordinates
        float r = Math.sqrt(sq(disX) + sq(disY));
        float angle = atan2(disY, disX);
        
        if (angle < 0) {
            // Shift to 0 - TWO_PI interval
            angle = TWO_PI + angle;
        }
    
        float start = this.angleStart % TWO_PI;
        float stop = this.angleStop % TWO_PI;
    
        if (r >= seg.donut.radius - seg.breadth / 2 && r <= seg.donut.radius + seg.breadth / 2 &&
           ((angle > start && angle < stop) || (start > stop && (angle > start || angle < stop)))) {
            seg.donut.setSelectedSlice(this);

            $('#slice-label').html(this.name);
            
            // Return true
            hovering = true;
        } else {
            hovering = false;
        }
    }
}

public class DonutSegment {
    String label;
    float weight;
    float angleStart;
    float angleStop;
    float breadth = STROKE_WEIGHT_COLLAPSED;
    Donut donut;
    ArrayList slices;
    color col;
    Tween fan;
    Tween breadthTween;
    
    public DonutSegment(String label, Donut donut) {
        this.label = label;
        this.donut = donut;
        this.slices = new ArrayList();
        this.weight = 1.0;
        this.fan = new Tween(this, "weight", Tween.strongEaseInOut, 1.0, 3.0, 0.8);
        this.breadthTween = new Tween(this, "breadth", Tween.strongEaseInOut, STROKE_WEIGHT_COLLAPSED, STROKE_WEIGHT_EXPANDED, 0.8);
    }
    
    // The resulting share of the segment (in percent)
    public float share() {
        return (slices.size() / donut.totalSlices()); // The share of total slices
    }
    
    // The weighted share of the segment in respect of the segment's weight (in percent)
    public float weightedShare() {
        return share() * weight / donut.totalWeight();
    }
    
    // The resulting size of the segment (in radiants)
    public float amount() {
        return (TWO_PI) * weightedShare();
    }
    
    public void update() {
        // Update local angleStart, angleStop using the shared angleOffset
        this.angleStart = donut.angleOffset;
    
        float amount = this.amount();
        float tmp = this.angleStart;
        
        for (int i = 0; i < slices.size(); i++) {
            slices[i].angleStart = tmp;
            slices[i].angleStop = tmp += amount / slices.size();
        }
    
        this.angleStop = donut.angleOffset += amount;
    }
    
    public void draw() {
        curContext.lineCap = "butt";
    
        fill(DONUT_BACKGROUND);
        noStroke();
    
        strokeWeight(this.breadth + 2 * OUTER_BORDER);
        stroke(DONUT_BACKGROUND);
        noFill();
        arc(donut.x, donut.y, (donut.radius) * 2, (donut.radius) * 2, this.angleStart, this.angleStop);
    
        fill(0, 0, 0, 0);
        stroke(col);
        strokeWeight(this.breadth);
    
        arc(donut.x, donut.y, donut.radius * 2, donut.radius*2, this.angleStart, this.angleStop);
    
        curContext.textAlign = "center";

        if (this === donut.selectedSegment) {
            fill(col);
    
            curContext.font = "16px Open Sans";
            curContext.fillText(this.label, donut.x, donut.y + 5);
        }

        // Draw slices
        for (int i = 0; i < slices.size(); i++) {
            slices[i].draw();
        }
    }
    
    // Start expanding tween
    public void expand() {
        this.fan.continueTo(3.0, 0.8);
        this.breadthTween.continueTo(STROKE_WEIGHT_EXPANDED, 0.8);
    }
    
    // Start contracting tween
    public void contract() {
        this.fan.continueTo(1.0, 0.8);
        this.breadthTween.continueTo(STROKE_WEIGHT_COLLAPSED, 0.8);
    }
    
    public void addSlice(DonutSlice s) {
        slices.add(s);
    }
}

public class Donut {
    float x;
    float y;
    float radius;
    float angleOffset = 0.0;
    ArrayList segments;
    int colorCount = 0;
    DonutSegment selectedSegment = null;
    DonutSlice selectedSlice = null;
    float angleAdjustment = 0.0;
    boolean opened = true;
    
    public Donut(float x, float y) {
        this.x = x;
        this.y = y;
        this.radius = 80;
        this.segments = new ArrayList();
    }
    
    public setSelectedSlice(DonutSlice s) {
        this.selectedSlice = s;
    
        if (s.seg != this.selectedSegment) {
            if (this.selectedSegment != null)

            if (this.selectedSegment != null) {
                this.selectedSegment.contract();
            }
    
            this.selectedSegment = s.seg;
            this.selectedSegment.expand();
        }
    }
    
    public void update() {
        this.angleAdjustment = 0.0;
        this.angleOffset = 0.0;
    
    
        if (selectedSlice != null) {
            float targetAngle = (selectedSlice.angleStart+selectedSlice.angleStop) / 2;
        }

        // Perform tweens first
        for (int i = 0; i < segments.size(); i++) {
            segments[i].fan.tick();
            segments[i].breadthTween.tick();
        }
    
        // Then update the values
        for (int i = 0; i < segments.size(); i++) {
            segments[i].update();
        }
    
        if (selectedSlice != null) {
            float offset = targetAngle - ((selectedSlice.angleStart+selectedSlice.angleStop) / 2);
    
            if (Math.abs(offset) > EPSILON) {
                float targetAdjustment = this.angleAdjustment+offset;
                if (targetAdjustment < 0) {
                    targetAdjustment = TWO_PI - targetAdjustment;
                }
    
                this.angleAdjustment = targetAdjustment;
            }
        }
    
        // Recalc again if angleAdjustment is needed.
        if (angleAdjustment > 0) {
            this.angleOffset = this.angleAdjustment;
            for (int i = 0; i < segments.size(); i++) {
                segments[i].update();
            }
        }
    }
    
    public float totalWeight() {
        float sum = 0.0;

        for (int i = 0; i < segments.size(); i++) {
            sum += segments[i].weight * segments[i].share();
        }
        
        return sum;
    }
    
    public float totalSlices() {
        int sum = 0;
        
        for (int i = 0; i < segments.size(); i++) {
            sum += segments[i].slices.size();
        }
        
        return sum;
    }
    
    public void draw() {
        noStroke();
    
        // Spacer
        fill(DONUT_BACKGROUND);
        ellipse(this.x, this.y, (radius + STROKE_WEIGHT_COLLAPSED / 2 + OUTER_BORDER) * 2,
                                (radius + STROKE_WEIGHT_COLLAPSED / 2 + OUTER_BORDER) * 2);
    
        // Draw attributes
        for (int i = 0 ; i < segments.size(); i++) {
            segments[i].draw();
        }
    }
    
    public void addSegment(DonutSegment s) {
        if (colorCount == 0)
            setSelectedSlice(s.slices[0]);
        
        s.col = colors[colorCount % 11]; // Assign a color
        
        segments.add(s);
        colorCount += 2;
    }
}

// Here we go!
Donut donut;

void setup() {
    size(250, 300);
    
    DonutSegment seg;
    donut = new Donut(125, 150);

    fixPerformance();

    // Do not remove this
    [[donut_data]]
}

void draw() {
    background(BACKGROUND);
    frameRate(30);
    donut.update();
    donut.draw();
    stroke(0);
    fill(0);
    
    pushMatrix();
    popMatrix();
}

void mouseMoved() {    
    for (int i = 0; i < donut.segments.size(); i++) {
        for (int j = 0; j < donut.segments[i].slices.size(); j++) {
            donut.segments[i].slices[j].checkSelected();
        }
    }
}

void fixPerformance() {
    hoverTimeout = setTimeout(function() {
        noLoop();
    }, 1000);

    $('canvas')
        .mouseover(function() {
            clearTimeout(hoverTimeout);
            loop();
        })
        .mouseout(function() {
            clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(function() {
                noLoop();
            }, 1000);
        });
}