import * as React from "react";
import { useTheme } from "@mui/material/styles";
import UIState from "../../../enums/UIState";
import { IconProps } from "../../../interfaces/Icons";

// The drawing of this icon assumes a coordinate system where the icon fits within
// a rectangle from [0,0] to [ARMY_RAW_ICON_WIDTH,ARMY_RAW_ICON_HEIGHT]
// This gets scaled a bit by WDUnitController
export const ARMY_RAW_ICON_WIDTH = 50;
export const ARMY_RAW_ICON_HEIGHT = 50;

const WDArmyIcon: React.FC<IconProps> = function ({
  country,
  iconState = UIState.NONE,
}): React.ReactElement {
  const theme = useTheme();

  return (
    <>
      {iconState === UIState.NONE && (
        <path
          d="M12.023 14a5 5 0 0 1 5-5h15.954a5 5 0 0 1 5 5v17.73a5 5 0 0 1-2.874 4.525l-7.977 3.747a5 5 0 0 1-4.252 0l-7.977-3.747a5 5 0 0 1-2.874-4.526V14Z"
          fill="#fff"
        />
      )}
      {iconState === UIState.HOLD && (
        <path
          d="M32.977 8H17.023a6 6 0 0 0-6 6v17.73a6 6 0 0 0 3.45 5.43l7.976 3.747a6 6 0 0 0 5.102 0l7.977-3.747a6 6 0 0 0 3.45-5.43V14a6 6 0 0 0-6-6Z"
          fill="#fff"
          stroke="#000"
          strokeWidth={4}
        />
      )}
      {iconState === UIState.DISLODGED && (
        <>
          <path
            d="M32.977 7H17.023a7 7 0 0 0-7 7v17.73a7 7 0 0 0 4.024 6.335l7.977 3.747a7 7 0 0 0 5.952 0l7.977-3.747a7 7 0 0 0 4.024-6.336V14a7 7 0 0 0-7-7Z"
            fill="#fff"
            stroke="#B00"
            strokeWidth={3}
          />
          <path
            stroke="#B00"
            strokeOpacity={0.5}
            strokeWidth={2}
            d="m13.293 37.293 26-26M26.172 7 10.501 22.672M40 26 24 42"
          />
        </>
      )}
      {(iconState === UIState.DISBANDED || iconState === UIState.DESTROY) && (
        <>
          <path
            d="M32.977 7H17.023a7 7 0 0 0-7 7v17.73a7 7 0 0 0 4.024 6.335l7.977 3.747a7 7 0 0 0 5.952 0l7.977-3.747a7 7 0 0 0 4.024-6.336V14a7 7 0 0 0-7-7Z"
            fill="#fff"
            stroke="#B00"
            strokeWidth={4}
          />
          <path
            stroke="#B00"
            strokeOpacity={0.5}
            strokeWidth={3}
            d="m13.293 37.293 26-26M26.172 7 10.501 22.672M40 26 24 42"
          />
        </>
      )}
      {iconState === UIState.BUILD && (
        <path
          d="M32.977 8.5H17.023a5.5 5.5 0 0 0-5.5 5.5v17.73a5.5 5.5 0 0 0 3.162 4.977l7.977 3.747a5.5 5.5 0 0 0 4.676 0l7.977-3.747a5.5 5.5 0 0 0 3.162-4.978V14a5.5 5.5 0 0 0-5.5-5.5Z"
          fill={theme.palette[country].light}
          stroke={theme.palette[country].main}
        />
      )}
      {
        false
        /* Pretty explosion, but not using it right now since it doesn't show the unit type or owner 
        (iconState === UIState.DISBANDED || iconState === UIState.DESTROY) && (
        <>
          <path
            d="m19.028 35.458-3.762 3.37.299-4.956L2.743 39.74l9.574-12.332-3.423-2.033 2.985-1.869L0 12.436l14.7 4.915-.85-15.133 5.75 9.707 1.21-5.97 3.386 8.745L39.23 0l-9.542 18.483 3.392.216-1.791 2.141L45 24.366l-13.125 3.15 3.093 3H30.44l5.446 9.013-10.943-5.544-4.946 8.122-.968-6.65Z"
            fill="#684C41"
          />
          <path
            d="m20.397 31.675-2.954 2.65.21-3.505-8.651 3.964 6.14-7.911-2.594-1.54 2.445-1.528-7.283-6.789 9.048 3.027-.571-10.145 4.292 7.247.901-4.442 2.085 5.374 8.672-8.483-5.476 10.603 2.548.16-1.313 1.575 9.218 2.372-9.043 2.167 2.208 2.147h-3.212l3.753 6.212-6.583-3.335-3.12 5.116-.72-4.936Z"
            fill="#F1906E"
          />
          <path
            d="m13.474 31.243 5.672-2.6-.15 2.466 2.384-2.136.54 3.71 1.807-2.97 3.48 1.766-2.554-4.22h2.275l-1.575-1.534 6.125-1.467-6.006-1.544.978-1.168-1.951-.124 2.568-4.972-4.128 4.035-1.147-2.97-.685 3.362-3.253-5.492.37 6.583-5.007-1.673 3.994 3.721-2.054 1.287 2.002 1.189-3.685 4.75Z"
            fill="#FEF189"
          />
        </>
        ) */
      }
      <path
        d="M34.065 20.963H27.58L25.024 14l-2.461 6.963H16.03l5.538 4.026L19.486 32l5.562-4.026L30.586 32l-2.036-6.987 5.515-4.05Z"
        fill={theme.palette[country].main}
        stroke={theme.palette[country].main}
        strokeWidth={1.5}
      />
    </>
  );
};

export default WDArmyIcon;
