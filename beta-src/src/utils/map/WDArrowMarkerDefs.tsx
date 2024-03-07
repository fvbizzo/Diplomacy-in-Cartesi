import * as React from "react";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import webDiplomacyTheme from "../../webDiplomacyTheme";

const WDArrowMarkerColors = function (
  arrowType: ArrowType,
): React.ReactElement {
  switch (arrowType) {
    case ArrowType.SUPPORT:
      return (
        <>
          {Object.entries(webDiplomacyTheme.palette.arrowColors).map(
            ([arrowColor, config]) => (
              <marker
                id={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                key={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                markerWidth={12}
                markerHeight={8}
                refX={18}
                refY={3}
                orient="auto"
              >
                <polygon
                  points="0 0, 6 3, 0 6, 0 5, 4 3, 0 1"
                  fill={config.main}
                />
                <polygon
                  points="4 0, 10 3, 4 6, 4 5, 8 3, 4 1"
                  fill={config.main}
                />
              </marker>
            ),
          )}
        </>
      );
    case ArrowType.HOLD:
      return (
        <>
          {Object.entries(webDiplomacyTheme.palette.arrowColors).map(
            ([arrowColor, config]) => (
              <marker
                id={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                key={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                markerWidth={90}
                markerHeight={90}
                refX={10}
                refY={45}
                orient="auto"
                markerUnits="userSpaceOnUse"
                strokeWidth={4}
              >
                <path d=" M 24 72 A 30 30 180 0 1 24 18" stroke={config.main} />
              </marker>
            ),
          )}
        </>
      );
    default:
      return (
        <>
          {Object.entries(webDiplomacyTheme.palette.arrowColors).map(
            ([arrowColor, config]) => (
              <marker
                id={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                key={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                markerWidth={8}
                markerHeight={8}
                refX={
                  (arrowColor as unknown as number) ===
                  ArrowColor.IMPLIED_FOREIGN
                    ? 0
                    : 7.1
                }
                refY={4}
                orient="auto"
              >
                <polygon points="0 0, 8 4, 0 8" fill={config.main} />
              </marker>
            ),
          )}
        </>
      );
  }
};

const WDArrowMarkerDefs = function (): React.ReactElement {
  return (
    <>
      {WDArrowMarkerColors(ArrowType.HOLD)}
      {WDArrowMarkerColors(ArrowType.MOVE)}
      {WDArrowMarkerColors(ArrowType.SUPPORT)}
    </>
  );
};

export default WDArrowMarkerDefs;
