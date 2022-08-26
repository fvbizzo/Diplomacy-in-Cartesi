import React, { ReactElement, FunctionComponent } from "react";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";

import { ReactComponent as StepOneIcon } from "../../../assets/svg/icons/stepOne.svg";
import { ReactComponent as StepTwoIcon } from "../../../assets/svg/icons/stepTwo.svg";
import {
  gameApiSliceActions,
  gameViewedPhase,
} from "../../../state/game/game-api-slice";

interface PhaseSelectorSimpleProps {
  viewedSeason: string;
  viewedYear: number;
  totalPhases: number;
}

const PhaseSelectorSimple: FunctionComponent<PhaseSelectorSimpleProps> =
  function ({
    viewedSeason,
    viewedYear,
    totalPhases,
  }: PhaseSelectorSimpleProps): ReactElement {
    const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
    const dispatch = useAppDispatch();
    const rightArrowsClassName =
      viewedPhaseIdx < totalPhases - 1
        ? "text-white"
        : "text-gray-600 cursor-not-allowed";

    return (
      <div className="bg-black flex text-white items-center h-14 px-4 sm:px-8 rounded-full space-x-3 sm:space-x-8 z-20 select-none">
        <button
          type="button"
          className="h-full"
          onClick={() => dispatch(gameApiSliceActions.setViewedPhase(0))}
        >
          <StepTwoIcon className="scale-x-[-1]" />
        </button>
        <button
          type="button"
          className="h-full"
          onClick={() =>
            dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(-1))
          }
        >
          <StepOneIcon className="scale-x-[-1] " />
        </button>
        <div className="text-center uppercase text-xs font-medium">
          <div>{viewedSeason}</div>
          <div>{viewedYear}</div>
        </div>
        <button
          type="button"
          className="h-full"
          onClick={() =>
            dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1))
          }
        >
          <StepOneIcon className={rightArrowsClassName} />
        </button>
        <button
          type="button"
          className="h-full"
          onClick={() =>
            dispatch(gameApiSliceActions.setViewedPhaseToLatestPhaseViewed())
          }
        >
          <StepTwoIcon className={rightArrowsClassName} />
        </button>
      </div>
    );
  };

export default PhaseSelectorSimple;
